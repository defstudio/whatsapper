<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;

class TextMessage implements WhatsappMessage
{
    use IsWhatsappMessage;



    public function __construct(
        protected string $text
    )
    {
    }

    public static function build(array $data): static
    {
        return new static($data['text']['body'])
            ->fillMessageData($data);
    }

    public function text(): string
    {
        return $this->text;
    }

    public function toRequestBody(): array
    {
        return [
            'type' => 'text',
            'text' => [
                'body' => $this->text,
            ],
        ];
    }
}
