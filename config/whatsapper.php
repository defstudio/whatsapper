<?php

// config for DefStudio/Whatsapper
use DefStudio\Whatsapper\Http\Controllers\WhatsapperWebhookController;

return [
    'debug' => env('WHATSAPPPER_DEBUG', false),
    'webhook' => [
        'path' => 'whatsapp/webhook',
        'controller' => WhatsapperWebhookController::class,
        'middleware' => [
            'api',
        ],
    ],
];
