<?php

namespace DefStudio\Whatsapper\Exceptions;

use Exception;
use Saloon\Http\Response;

class WhatsapperMediaException extends Exception
{
    public static function failedToDownload(string $id, Response $response): WhatsapperMediaException
    {
        return new self("Failed to download media [#$id]: ".$response->body());
    }

    public static function mediaNotStored(string $id): WhatsapperMediaException
    {
        return new self("Media [#$id] was not stored");
    }
}
