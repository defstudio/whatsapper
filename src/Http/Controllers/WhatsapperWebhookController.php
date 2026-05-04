<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpClassConstantAccessedViaChildClassInspection */

namespace DefStudio\Whatsapper\Http\Controllers;

use DefStudio\Whatsapper\Contracts\WhatsappWebhookController as Contract;
use DefStudio\Whatsapper\Events\WhatsappMessageReceived;
use DefStudio\Whatsapper\Events\WhatsappMessageStatusUpdated;
use DefStudio\Whatsapper\Events\WhatsappOtherWebhookEventReceived;
use DefStudio\Whatsapper\Events\WhatsappWebhookReceived;
use DefStudio\Whatsapper\Facades\Whatsapper;
use DefStudio\Whatsapper\Support\WhatsappWebhookPayload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsapperWebhookController implements Contract
{
    public function verify(Request $request): Response
    {
        if (! Whatsapper::isWebhookEnabled()) {
            abort(404);
        }

        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));

        if ($mode !== 'subscribe') {
            return response('Invalid hub mode', Response::HTTP_BAD_REQUEST);
        }

        if (! Whatsapper::verifyWebhook($token)) {
            return response('Invalid verify token', Response::HTTP_FORBIDDEN);
        }

        return response((string) $challenge, Response::HTTP_OK);
    }

    public function handle(Request $request): Response
    {
        if (! Whatsapper::isWebhookEnabled()) {
            abort(404);
        }

        if (config('whatsapper.debug')) {
            logger('[WHATSAPPER webhook] payload received', $request->all());
        }

        $payload = new WhatsappWebhookPayload($request->all());

        event(new WhatsappWebhookReceived($payload));

        foreach ($payload->messages() as $message) {
            event(new WhatsappMessageReceived(
                messageData: $message['message'],
                metadata: $message['metadata'],
                contacts: $message['contacts'],
                rawChange: $message['change'],
                rawPayload: $payload->payload,
            ));
        }

        foreach ($payload->statuses() as $status) {
            event(new WhatsappMessageStatusUpdated(
                status: $status['status'],
                metadata: $status['metadata'],
                rawChange: $status['change'],
                rawPayload: $payload->payload,
            ));
        }

        foreach ($payload->otherChanges() as $change) {
            event(new WhatsappOtherWebhookEventReceived(
                field: $change['field'] ?? 'unknown',
                value: $change['value'] ?? [],
                rawChange: $change,
                rawPayload: $payload->payload,
            ));
        }

        return response()->noContent(Response::HTTP_OK);
    }
}
