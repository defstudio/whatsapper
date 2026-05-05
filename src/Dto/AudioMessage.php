<?php /** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsMediaMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;
use Exception;
use Illuminate\Support\Facades\File;
use Symfony\Component\Mime\MimeTypes;

class AudioMessage implements WhatsappMessage
{
    use IsWhatsappMessage;
    use IsMediaMessage;

    protected bool $voice;


    protected function shouldAutoStore(): bool
    {
        return Whatsapper::shouldStoreAudio();
    }

    public static function build(array $data): static
    {
        $message = new static(
            $data['audio']['id'],
            $data['audio']['url'],
            $data['audio']['mime_type'],
        )->fillMessageData($data);

        $message->voice = $data['audio']['voice'] ?? false;

        return $message;
    }

    public function isVoice(): bool
    {
        return $this->voice;
    }
}
