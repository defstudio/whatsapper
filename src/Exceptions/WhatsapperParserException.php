<?php

namespace DefStudio\Whatsapper\Exceptions;

use Exception;

class WhatsapperParserException extends Exception
{
    public static function unsupportedType(string $id, string $type): WhatsapperParserException
    {
        return new self("Unsupported message type: $type [message id: $id]");
    }

    public static function missingUserId(): WhatsapperParserException
    {
        return new self('whatsapp user id is missing from the message');
    }

    public static function missingPhoneNumber(): WhatsapperParserException
    {
        return new self('phone number is missing from the message');
    }
}
