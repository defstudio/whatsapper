<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto\Concerns;

use DefStudio\Whatsapper\Exceptions\WhatsapperMediaException;
use DefStudio\Whatsapper\Facades\Whatsapper;
use DefStudio\Whatsapper\Support\MediaSafety;
use Illuminate\Support\Facades\File;
use Saloon\Http\Response;

trait IsMediaMessage
{
    protected string $mediaId;

    protected string $mediaUrl;

    protected string $mediaMimeType;

    protected string $mediaPath;

    abstract protected function shouldAutoStore(): bool;

    public function __construct(string $mediaId, string $mediaUrl, string $mediaMimeType)
    {
        MediaSafety::validateId($mediaId);

        $this->mediaId = $mediaId;
        $this->mediaUrl = $mediaUrl;
        $this->mediaMimeType = $mediaMimeType;

        if ($this->shouldAutoStore()) {
            $path = Whatsapper::mediaPath()."/$this->mediaId.{$this->extension()}";

            if (! Whatsapper::mediaDisk()->put($path, $this->download()->body())) {
                throw WhatsapperMediaException::failedToStore($this->mediaId);
            }

            $this->mediaPath = Whatsapper::mediaDisk()->path($path);
        }
    }

    public function store(string $path): string
    {
        File::put($path, $this->download()->body());

        return $path;
    }

    protected function download(): Response
    {
        $response = Whatsapper::getMedia($this->mediaId);

        if (! $response->successful()) {
            throw WhatsapperMediaException::failedToDownload($this->mediaId, $response);
        }

        return $response;
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

        return match ($normalized) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/3gpp' => '3gp',
            'audio/aac' => 'aac',
            'audio/amr' => 'amr',
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/ogg' => 'ogg',
            default => 'bin',
        };
    }
}
