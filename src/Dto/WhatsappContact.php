<?php

namespace DefStudio\Whatsapper\Dto;

readonly class WhatsappContact
{
    public function __construct(
        public string $userId,
        public string $phoneNumber,
        public ?string $name = null,
    )
    {
    }

}
