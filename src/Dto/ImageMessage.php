<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsMediaMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;

class ImageMessage implements WhatsappMessage
{
    use IsMediaMessage;
    use IsWhatsappMessage;

    protected function shouldAutoStore(): bool
    {
        return Whatsapper::shouldStoreImages();
    }

    public static function build(array $data): static
    {
        return new static(
            $data['image']['id'],
            $data['image']['url'],
            $data['image']['mime_type'],
        )->fillMessageData($data);
    }
}
