<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ImageMessage implements WhatsappMessage
{
    use IsWhatsappMessage;

    protected string $imageId;
    protected string $imageUrl;
    protected string $imageMimeType;

    public function __construct(string $imageId, string $imageUrl, string $imageMimeType = 'image/jpeg')
    {
        $this->imageId = $imageId;
        $this->imageUrl = $imageUrl;
        $this->imageMimeType = $imageMimeType;
    }


    public function toRequestBody(): array
    {
        throw new Exception('Not implemented.');
    }

    public static function build(array $data): static
    {
        return new static(
            $data['image']['id'],
            $data['image']['url'],
            $data['image']['mime_type'],
        );
    }

    public function text(): string
    {
        return "<IMAGE #$this->imageId>";
    }

    public function store(string $path): bool
    {
        $response = Whatsapper::getMedia($this->imageId, $this->imageUrl);

        if (!$response->successful()) {
            throw new Exception('Failed to download image: '.$response->body());
        }

        File::put($path, $response->body());

        return true;
    }
}
