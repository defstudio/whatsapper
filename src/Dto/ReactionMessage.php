<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;

class ReactionMessage implements WhatsappMessage
{
    use IsWhatsappMessage;

    public function __construct(
        protected string $text
    ) {}

    public static function build(array $data): static
    {
        $reaction = $data['reaction'];
        $text = $reaction['emoji'];


        $reaction_message = new static($text)
            ->fillMessageData($data);

        $reaction_message->context = new WhatsappMessageContext($data['from'], $reaction['message_id']);

        return $reaction_message;
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
