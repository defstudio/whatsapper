<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Concerns\WhatsappMessage;
use Saloon\Traits\Makeable;

class TextMessage implements WhatsappMessage
{
    use Makeable;

    protected string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
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
