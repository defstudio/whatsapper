<?php

namespace DefStudio\Whatsapper\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface WhatsappWebhookController
{
    public function verify(Request $request): Response;

    public function handle(Request $request): JsonResponse;
}
