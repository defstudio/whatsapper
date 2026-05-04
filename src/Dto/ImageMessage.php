<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\MimeTypes;

class ImageMessage implements WhatsappMessage
{
    use IsWhatsappMessage;

    protected string $imageId;
    protected string $imageUrl;
    protected string $imageMimeType;

    public function __construct(string $imageId, string $imageUrl, string $imageMimeType)
    {
        $this->imageId = $imageId;
        $this->imageUrl = $imageUrl;
        $this->imageMimeType = $imageMimeType;

        if (config('whatsapper.webhook.images.store')) {
            $this->store(Storage::disk(config('whatsapper.webhook.images.disk'))
                ->path(config('whatsapper.webhook.images.path', 'whatsapp/media')."/$this->imageId.{$this->extension()}")
            );
        }
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

        return $path;
    }

    public function extension(): string
    {
        $normalized = strtolower(trim(explode(';', $this->imageMimeType)[0]));

        $extensions = MimeTypes::getDefault()->getExtensions($normalized);

        if ($extensions !== []) {
            return $extensions[0];
        }

        return $this->fallbackExtensionFromMimeType($normalized);
    }

    protected function fallbackExtensionFromMimeType(string $mimeType): string
    {
        if (!str_contains($mimeType, '/')) {
            return 'bin';
        }

        $subtype = explode('/', $mimeType, 2)[1];
        $subtype = explode('+', $subtype, 2)[0];
        $subtype = explode('.', $subtype, 2)[0];

        return preg_replace('/[^a-z0-9]/', '', $subtype) ?: 'bin';
    }
}
