<?php

use DefStudio\Whatsapper\Whatsapp;

if (!function_exists('whatsapp')) {
    function whatsapp(): Whatsapp
    {
        return new Whatsapp();
    }
}
