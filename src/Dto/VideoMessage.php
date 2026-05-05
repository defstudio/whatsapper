<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsMediaMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;

class VideoMessage implements WhatsappMessage
{
    use IsMediaMessage;
    use IsWhatsappMessage;

    protected function shouldAutoStore(): bool
    {
        return Whatsapper::shouldStoreVideos();
    }

    public static function build(array $data): static
    {
        return new static(
            $data['video']['id'],
            $data['video']['url'],
            $data['video']['mime_type'],
        )->fillMessageData($data);
    }
}
