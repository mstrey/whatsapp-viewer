<?php
declare(strict_types=1);

class ChatLayoutHelper {
    private array $senderMapping;

    public function __construct(array $globalMapping = []) {
        $this->senderMapping = $globalMapping;
    }

    public function getClassForSender(string $sender): string {
        if (!isset($this->senderMapping[$sender])) {
            // Fallback caso alguém novo fale no meio da conversa
            $this->senderMapping[$sender] = empty($this->senderMapping) ? 'incoming' : 'outgoing';
        }
        return $this->senderMapping[$sender];
    }
}