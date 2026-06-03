<?php
declare(strict_types=1);

class WhatsAppParser {
    private string $chatDirectory;

    public function __construct(string $directory) {
        $this->chatDirectory = $directory;
    }

    private function getTargetFile(): ?string {
        if (!is_dir($this->chatDirectory)) {
            throw new RuntimeException("O diretório de dados não existe.");
        }

        $files = glob($this->chatDirectory . '/*.[tT][xX][tT]');
        
        if (empty($files)) {
            $dirs = glob($this->chatDirectory . '/*', GLOB_ONLYDIR);
            if (!empty($dirs)) {
                throw new RuntimeException("Subpasta detectada. Coloque o .txt na raiz de chat_data.");
            }
            return null;
        }
        return $files[0];
    }

private function getTotalMessagesCount(string $chatFile): int {
        $fileHash = md5_file($chatFile);
        
        $tempDir = sys_get_temp_dir();
        $cacheFile = $tempDir . '/whatsapp_meta_count_' . $fileHash;
        
        if (file_exists($cacheFile)) {
            $cachedCount = file_get_contents($cacheFile);
            if ($cachedCount !== false) {
                return (int) $cachedCount;
            }
        }

        $oldCaches = glob($tempDir . '/whatsapp_meta_count_*');
        if (is_array($oldCaches)) {
            array_map('unlink', $oldCaches);
        }

        $count = 0;
        $handle = fopen($chatFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^\[?\d{2}\/\d{2}\/\d{2,4}/', $line)) {
                    $count++;
                }
            }
            fclose($handle);
        }
        
        @file_put_contents($cacheFile, (string) $count);
        
        return $count;
    }

    public function getPaginatedMessages(int $page = 1, int $perPage = 300): array {
        $chatFile = $this->getTargetFile();
        
        if (!$chatFile) {
            return ['messages' => [], 'totalPages' => 0, 'currentPage' => 1];
        }

        $totalMessages = $this->getTotalMessagesCount($chatFile);
        
        if ($totalMessages === 0) {
            return ['messages' => [], 'totalPages' => 0, 'currentPage' => 1];
        }

        $totalPages = (int) ceil($totalMessages / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $limit = $offset + $perPage;

        $messages = [];
        $currentIndex = -1;
        $currentMessage = null;
        
        $handle = fopen($chatFile, 'r');
        if (!$handle) {
            throw new RuntimeException("Falha de permissão ao ler o arquivo de log.");
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Expressão regular completa para extrair dados da mensagem
            if (preg_match('/^\[?(\d{2}\/\d{2}\/\d{2,4})[, ]+(\d{2}:\d{2}(?::\d{2})?)\]?[\s\-]+(.*?): (.*)$/', $line, $matches)) {
                
                // Salva a mensagem anterior se estiver dentro do range exibido
                if ($currentMessage !== null && $currentIndex >= $offset) {
                    $messages[] = $currentMessage;
                }

                $currentIndex++;

                // Interrompe a leitura física do disco para economizar I/O e CPU
                if ($currentIndex >= $limit) {
                    break;
                }

                if ($currentIndex >= $offset) {
                    $currentMessage = [
                        'date' => $matches[1],
                        'time' => $matches[2],
                        'sender' => trim($matches[3]),
                        'text' => htmlspecialchars(trim($matches[4]), ENT_QUOTES, 'UTF-8')
                    ];
                } else {
                    $currentMessage = null;
                }
            } else {
                // Tratamento de mensagens com quebra de linha
                if ($currentMessage !== null && $currentIndex >= $offset) {
                    $currentMessage['text'] .= '<br>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        if ($currentMessage !== null && $currentIndex >= $offset && $currentIndex < $limit) {
            $messages[] = $currentMessage;
        }

        fclose($handle);

        return [
            'messages' => $messages,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

    public function getGlobalSenderMapping(): array {
        $chatFile = $this->getTargetFile();
        if (!$chatFile) {
            return [];
        }

        $mapping = [];
        $handle = fopen($chatFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^\[?(\d{2}\/\d{2}\/\d{2,4})[, ]+(\d{2}:\d{2}(?::\d{2})?)\]?[\s\-]+(.*?): /', $line, $matches)) {
                    $sender = trim($matches[3]);
                    if (!isset($mapping[$sender])) {
                        if (empty($mapping)) {
                            $mapping[$sender] = 'incoming';
                        } else {
                            $mapping[$sender] = 'outgoing';
                            break;
                        }
                    }
                }
            }
            fclose($handle);
        }
        return $mapping;
    }
}