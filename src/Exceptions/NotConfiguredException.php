<?php

namespace DefStudio\Whatsapper\Exceptions;

use Exception;

class NotConfiguredException extends Exception
{
    public static function make(): NotConfiguredException
    {
        return new self("Whatsapper is not configured");
    }
}
