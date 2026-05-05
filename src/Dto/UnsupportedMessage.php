<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use Exception;

class UnsupportedMessage implements WhatsappMessage
{
    use IsWhatsappMessage;

    public function __construct() {}

    public function text(): string
    {
        return '_Errore: Questo tipo di messaggi non è supportato_';
    }

    public static function build(array $data): static
    {
        return new static()
            ->fillMessageData($data);
    }
}
