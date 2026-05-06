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
        'payloads' => [
            'disk' => env('WHATSAPPER_PAYLOADS_DISK'),
            'path' => env('WHATSAPPER_PAYLOADS_PATH', 'whatsapp/payloads'),
            'status_changes' => [
                'store' => env('WHATSAPPER_STORE_STATUS_CHANGES', true),
            ],
            'messages' => [
                'store' => env('WHATSAPPER_STORE_MESSAGES', true),
            ],
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
