<?php

use DefStudio\Whatsapper\Facades\Whatsapper;

if (! Whatsapper::isWebhookEnabled()) {
    return;
}

Illuminate\Support\Facades\Route::middleware(Whatsapper::getWebhookMiddleware())
    ->group(function () {
        Route::get(Whatsapper::getWebhookPath(), [Whatsapper::getWebhookControllerClass(), 'verify'])
            ->name('whatsapper.webhook.verify');

        Route::post(Whatsapper::getWebhookPath(), [Whatsapper::getWebhookControllerClass(), 'handle'])
            ->name('whatsapper.webhook.handle');
    });
