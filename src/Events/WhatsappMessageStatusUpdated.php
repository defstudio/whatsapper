<?php

namespace DefStudio\Whatsapper\Events;

class WhatsappMessageStatusUpdated
{
    public function __construct(
        public readonly array $status,
        public readonly ?array $metadata,
        public readonly array $rawChange,
        public readonly array $rawPayload,
    ) {
    }
}
