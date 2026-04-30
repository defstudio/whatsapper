<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;

class ButtonMessage implements WhatsappMessage
{
    use IsWhatsappMessage;


    protected string $text;
    protected string $payload;

    public function __construct(string $text, string $payload)
    {
        $this->text = $text;
        $this->payload = $payload;
    }


    public static function build(array $data): static
    {
        return new static($data['button']['body'], $data['button']['payload'])
            ->fillMessageData($data);
    }

    public function toRequestBody(): array
    {
        return [
            'type' => 'button',
            'button' => [
                'text' => $this->text,
                'payload' => $this->payload,
            ],
        ];
    }
}
