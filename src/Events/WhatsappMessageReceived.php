<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Events;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\ButtonMessage;
use DefStudio\Whatsapper\Dto\TextMessage;
use DefStudio\Whatsapper\Exceptions\WhatsapperParserException;

readonly class WhatsappMessageReceived
{
    public WhatsappMessage $message;

    public function __construct(
        public array $messageData,
        public ?array $metadata,
        public ?array $contacts,
        public array $rawChange,
        public array $rawPayload,
    ) {

        $this->message = match ($this->messageData['type']) {
            'text' => TextMessage::build($this->messageData),
            'button' => ButtonMessage::build($this->messageData),
            default => throw WhatsapperParserException::unsupportedType($this->messageData['type']),
        };

        $this->message->fillContact($this->contacts[0] ?? []);
    }
}
