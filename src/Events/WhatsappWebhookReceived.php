<?php

namespace DefStudio\Whatsapper\Events;

use DefStudio\Whatsapper\Support\WhatsappWebhookPayload;

class WhatsappWebhookReceived
{
    public function __construct(
        public readonly WhatsappWebhookPayload $payload,
    ) {}
}
