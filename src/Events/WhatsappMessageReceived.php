<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Events;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\AudioMessage;
use DefStudio\Whatsapper\Dto\ButtonMessage;
use DefStudio\Whatsapper\Dto\ImageMessage;
use DefStudio\Whatsapper\Dto\TextMessage;
use DefStudio\Whatsapper\Dto\UnsupportedMessage;
use DefStudio\Whatsapper\Dto\VideoMessage;

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
            'image' => ImageMessage::build($this->messageData),
            'video' => VideoMessage::build($this->messageData),
            'audio' => AudioMessage::build($this->messageData),
            default => UnsupportedMessage::build($this->messageData),
        };

        $this->message->fillContact($this->contacts[0] ?? []);
    }
}
