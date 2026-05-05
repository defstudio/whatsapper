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
        'media' => [
            'disk' => env('WHATSAPPER_MEDIA_DISK'),
            'path' => env('WHATSAPPER_MEDIA_PATH', 'whatsapp/media'),
            'images' => [
                'store' => env('WHATSAPPER_STORE_IMAGES', true),
            ],
            'videos' => [
                'store' => env('WHATSAPPER_STORE_VIDEOS', true),
            ],
            'audio' => [
                'store' => env('WHATSAPPER_STORE_AUDIO', true),
            ],
        ],
        'middleware' => [
            'api',
        ],
    ],
];
