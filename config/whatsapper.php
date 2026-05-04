<?php

// config for DefStudio/Whatsapper
use DefStudio\Whatsapper\Http\Controllers\WhatsapperWebhookController;

return [
    'debug' => [
        'enabled' => env('WHATSAPPER_DEBUG', false),
    ],
    'webhook' => [
        'path' => 'whatsapp/webhook',
        'controller' => WhatsapperWebhookController::class,
        'messages' => [
            'store' => env('WHATSAPPER_STORE_MESSAGES', true),
            'disk' => env('WHATSAPPER_MESSAGES_DISK'),
            'path' => env('WHATSAPPER_MESSAGES_PATH', 'whatsapp/messages'),
        ],
        'middleware' => [
            'api',
        ],
    ],
];
