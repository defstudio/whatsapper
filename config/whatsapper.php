<?php

// config for DefStudio/Whatsapper
use DefStudio\Whatsapper\Http\Controllers\WhatsapperWebhookController;

return [
    'webhook' => [
        'path' => 'whatsapp/webhook',
        'controller' => WhatsapperWebhookController::class,
        'middleware' => [
            'api'
        ],
    ]
];
