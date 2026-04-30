<?php

namespace DefStudio\Whatsapper\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface WhatsappWebhookController
{
    public function verify(Request $request): Response;

    public function handle(Request $request): Response;
}
