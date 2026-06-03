<?php
declare(strict_types=1);

class MediaHelper {
    public static function renderBody(string $text): string {
        $pattern = '/([a-zA-Z0-9_\-\s]+\.(opus|pdf|jpg|jpeg|png|gif))/i';

        return preg_replace_callback($pattern, function($matches) {
            $fileName = trim($matches[1]);
            $extension = strtolower($matches[2]);
            $filePath = '/chat_data/' . rawurlencode($fileName);

            switch ($extension) {
                case 'opus':
                    return "<div class='media-attachment audio-attachment'>" .
                           "<p>Mensagem de Voz:</p>" .
                           "<audio controls preload='none' aria-label='Áudio anexado: {$fileName}'>" .
                           "<source src='{$filePath}' type='audio/ogg; codecs=opus'>" .
                           "Seu navegador não suporta a reprodução deste áudio." .
                           "</audio>" .
                           "</div>";

                case 'pdf':
                    return "<div class='media-attachment doc-attachment'>" .
                           "<p class='doc-title'>📄 Documento PDF anexado:</p>" .
                           "<a href='{$filePath}' class='btn-download' target='_blank' rel='noopener noreferrer' aria-label='Abrir documento PDF {$fileName} em nova aba'>" .
                           "Abrir " . htmlspecialchars($fileName) .
                           "</a>" .
                           "</div>";

                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    return "<div class='media-attachment img-attachment'>" .
                           "<a href='{$filePath}' target='_blank' rel='noopener noreferrer'>" .
                           "<img src='{$filePath}' alt='Imagem anexada: {$fileName}' class='chat-img' loading='lazy'>" .
                           "</a>" .
                           "</div>";

                default:
                    return htmlspecialchars($fileName);
            }
        }, $text);
    }
}