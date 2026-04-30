<?php

use DefStudio\Whatsapper\Http\Controllers\WhatsapperWebhookController;

Illuminate\Support\Facades\Route::middleware(config('whatsapper.webhook.middleware', ['api']))
    ->group(function () {
        Route::get(config('whatsapper.webhook.path', 'whatsapp/webhook'), [config('whatsapper.webhook.controller', WhatsapperWebhookController::class), 'verify'])
            ->name('whatsapper.webhook.verify');

        Route::post(config('whatsapper.webhook.path', 'whatsapp/webhook'), [config('whatsapper.webhook.controller', WhatsapperWebhookController::class), 'handle'])
            ->name('whatsapper.webhook.handle');
    });
