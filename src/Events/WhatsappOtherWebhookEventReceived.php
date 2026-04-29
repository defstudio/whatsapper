<?php

namespace DefStudio\Whatsapper\Events;

class WhatsappOtherWebhookEventReceived
{
    public function __construct(
        public readonly string $field,
        public readonly array $value,
        public readonly array $rawChange,
        public readonly array $rawPayload,
    ) {}
}
