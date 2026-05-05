<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsMediaMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;
use Exception;
use Illuminate\Support\Facades\File;
use Symfony\Component\Mime\MimeTypes;

class VideoMessage implements WhatsappMessage
{
    use IsWhatsappMessage;
    use IsMediaMessage;

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
