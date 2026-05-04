<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use Exception;

class UnsupportedMessage implements WhatsappMessage
{
    use IsWhatsappMessage;

    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function text(): string
    {
        return '_Errore: Questo tipo di messaggi non è supportato_';
    }

    public function toRequestBody(): array
    {
        throw new Exception('Not implemented.');
    }

    public static function build(array $data): static
    {
        return new static($data['type']);
    }

    public function type(): string
    {
        return $this->type;
    }
}
