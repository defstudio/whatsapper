<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto\Concerns;

use DefStudio\Whatsapper\Exceptions\WhatsapperMediaException;
use DefStudio\Whatsapper\Facades\Whatsapper;
use Illuminate\Support\Facades\File;
use Symfony\Component\Mime\MimeTypes;

trait IsMediaMessage
{
    protected string $mediaId;

    protected string $mediaUrl;

    protected string $mediaMimeType;

    protected string $mediaPath;

    protected abstract function shouldAutoStore(): bool;

    public function __construct(string $mediaId, string $mediaUrl, string $mediaMimeType)
    {
        $this->mediaId = $mediaId;
        $this->mediaUrl = $mediaUrl;
        $this->mediaMimeType = $mediaMimeType;

        if ($this->shouldAutoStore()) {
            Whatsapper::mediaDisk()->makeDirectory(Whatsapper::mediaPath());

            $this->mediaPath = $this->store(Whatsapper::mediaDisk()
                ->path(Whatsapper::mediaPath()."/$this->mediaId.{$this->extension()}")
            );
        }
    }


    public function store(string $path): string
    {
        $response = Whatsapper::getMedia($this->mediaId, $this->mediaUrl);

        if (!$response->successful()) {
            throw WhatsapperMediaException::failedToDownload($this->mediaId, $response);
        }

        File::put($path, $response->body());

        return $path;
    }

    public function path(): string
    {
        return $this->mediaPath ?? throw WhatsapperMediaException::mediaNotStored($this->mediaId);
    }


    public function text(): string
    {
        return "<MEDIA #$this->mediaId>";
    }


    public function extension(): string
    {
        $normalized = strtolower(trim(explode(';', $this->mediaMimeType)[0]));

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
