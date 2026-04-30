<?php

namespace DefStudio\Whatsapper\Dto;

readonly class WhatsappMessageContext
{
    public function __construct(
        public string $from,
        public string $messageId,
    ) {}

}
