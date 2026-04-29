<?php

namespace DefStudio\Whatsapper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DefStudio\Whatsapper\Whatsapper
 */
class Whatsapper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DefStudio\Whatsapper\Whatsapper::class;
    }
}
