<?php

namespace DefStudio\Whatsapper\Exceptions;

use DefStudio\Whatsapper\Contracts\WhatsappWebhookController;
use Exception;

class WhatsapperConfigurationException extends Exception
{
    public static function sendingNotConfigured(): WhatsapperConfigurationException
    {
        return new self('Whatsapper message sending is not configured');
    }

    public static function webhookControllerMustImplementContract(): WhatsapperConfigurationException
    {
        return new self('Whatsapper webhook controller must implement '.WhatsappWebhookController::class);
    }
}
