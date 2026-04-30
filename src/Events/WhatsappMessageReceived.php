<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Events;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\ButtonMessage;
use DefStudio\Whatsapper\Dto\TextMessage;
use DefStudio\Whatsapper\Exceptions\WhatsapperParserException;

readonly class WhatsappMessageReceived
{
    public function __construct(
        public array $message,
        public ?array $metadata,
        public ?array $contacts,
        public array $rawChange,
        public array $rawPayload,
    ) {}

    public function message(): WhatsappMessage
    {
        $message = match ($this->message['type']) {
            'text' => TextMessage::build($this->message),
            'button' => ButtonMessage::build($this->message),
            default => throw WhatsapperParserException::unsupportedType($this->message['type']),
        };

        $message->fillContact($this->contacts[0] ?? []);

        return $message;
    }
}
