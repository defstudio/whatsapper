<?php

namespace DefStudio\Whatsapper\Exceptions;

use Exception;
use Saloon\Http\Response;

class WhatsapperMediaException extends Exception
{
    public static function invalidId(): self
    {
        return new self('Invalid Whatsapper media identifier');
    }

    public static function invalidDownloadUrl(): self
    {
        return new self('Media downloads require an HTTPS URL on a trusted Meta domain');
    }

    public static function failedToStore(string $id): self
    {
        return new self("Failed to store media [#$id]");
    }

    public static function failedToDownload(string $id, Response $response): WhatsapperMediaException
    {
        return new self("Failed to download media [#$id]: ".$response->body());
    }

    public static function mediaNotStored(string $id): WhatsapperMediaException
    {
        return new self("Media [#$id] was not stored");
    }
}
