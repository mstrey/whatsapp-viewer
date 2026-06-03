<?php
declare(strict_types=1);
require_once __DIR__ . '/WhatsAppParser.php';
require_once __DIR__ . '/MediaHelper.php';
require_once __DIR__ . '/ChatLayoutHelper.php';

$parser = new WhatsAppParser(__DIR__ . '/chat_data');
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = 300;
$globalMapping = $parser->getGlobalSenderMapping();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador de Conversa Exportada</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
</head>
<body>
    <main class="chat-container" aria-label="Histórico de Conversas do WhatsApp">
        <header class="chat-header">
            <h1>Histórico de Mensagens</h1>
            <div class="controls">
                <label for="searchInput">Pesquisar na conversa:</label>
                <input type="search" id="searchInput" placeholder="Digite para buscar..." aria-controls="messageList">
            </div>
        </header>

        <section id="messageList" class="message-list" aria-live="polite">
            <?php 
            try {
                $paginatedData = $parser->getPaginatedMessages($page, $perPage);
                $messages = $paginatedData['messages'];
                $totalPages = $paginatedData['totalPages'];
                $currentPage = $paginatedData['currentPage'];
                
                $layoutHelper = new ChatLayoutHelper($globalMapping);
                
                if (empty($messages)) { 
                    echo '<p class="sys-message" role="alert">Nenhum arquivo .txt encontrado.</p>';
                } else {
                    foreach ($messages as $msg) { 
                        $alignmentClass = $layoutHelper->getClassForSender($msg['sender']);
            ?>
                        <article class="message <?php echo $alignmentClass; ?>">
                            <header class="message-meta">
                                <span class="sender"><?php echo htmlspecialchars($msg['sender']); ?></span>
                                <time datetime="<?php echo $msg['date'] . ' ' . $msg['time']; ?>">
                                    <?php echo $msg['date'] . ' às ' . $msg['time']; ?>
                                </time>
                            </header>
                            <div class="message-body">
                                <?php echo MediaHelper::renderBody($msg['text']); ?>
                            </div>
                        </article>
            <?php
                    } 
                }
            } catch (RuntimeException $e) {
                echo '<p class="sys-message" role="alert"><strong>Erro:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                $totalPages = 0;
            }
            ?>
        </section>

        <?php if (isset($totalPages) && $totalPages > 1) { ?>
        <nav aria-label="Navegação de páginas das mensagens" class="pagination-container">
            <div class="pagination-jump">
                <form action="" method="GET">
                    <label for="pageInput">Ir para página:</label>
                    <input type="number" id="pageInput" name="page" min="1" max="<?php echo $totalPages; ?>" value="<?php echo $currentPage; ?>">
                    <button type="submit">Ir</button>
                </form>
            </div>
            
            <ul class="pagination-list">
                <?php if ($currentPage > 1) { ?>
                    <li><a href="?page=<?php echo $currentPage - 1; ?>" aria-label="Página anterior">&laquo; Anterior</a></li>
                <?php } ?>
                
                <li class="page-info">Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?></li>
                
                <?php if ($currentPage < $totalPages) { ?>
                    <li><a href="?page=<?php echo $currentPage + 1; ?>" aria-label="Próxima página">Próxima &raquo;</a></li>
                <?php } ?>
            </ul>
        </nav>
        <?php } ?>
    </main>
    <script src="script.js" defer></script>
</body>
</html>