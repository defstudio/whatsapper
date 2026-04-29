<?php

namespace DefStudio\Whatsapper\Events;

class WhatsappMessageReceived
{
    public function __construct(
        public readonly array $message,
        public readonly ?array $metadata,
        public readonly ?array $contacts,
        public readonly array $rawChange,
        public readonly array $rawPayload,
    ) {}
}
