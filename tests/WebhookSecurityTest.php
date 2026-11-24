<?php

use DefStudio\Whatsapper\Events\WhatsappMessageReceived;
use DefStudio\Whatsapper\Events\WhatsappWebhookReceived;
use DefStudio\Whatsapper\Exceptions\WhatsapperConfigurationException;
use DefStudio\Whatsapper\Facades\Whatsapper;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Saloon\Http\Faking\MockClient;

beforeEach(function () {
    MockClient::destroyGlobal();
    MockClient::global([]);
    Storage::fake('webhook-security');
    config()->set('whatsapper.webhook.payloads.disk', 'webhook-security');
    config()->set('whatsapper.webhook.media.disk', 'webhook-security');
    config()->set('whatsapper.webhook.app_secret', null);
});

afterEach(fn () => MockClient::destroyGlobal());

it('requires an app secret before enabling webhooks', function () {
    expect(fn () => Whatsapper::enableWebhook('verification-token'))
        ->toThrow(WhatsapperConfigurationException::class);
    expect(Whatsapper::isWebhookEnabled())->toBeFalse();
});

it('supports configuring the app secret through config', function () {
    config()->set('whatsapper.webhook.app_secret', 'test-app-secret');
    Whatsapper::enableWebhook('verification-token');
    $body = '{"entry":[]}';
    expect(Whatsapper::verifyWebhookSignature($body, 'sha256='.hash_hmac('sha256', $body, 'test-app-secret')))->toBeTrue();
});

it('rejects unauthenticated posts before logging events storage or downloads', function (?string $signature) {
    Whatsapper::enableWebhook('verification-token', 'test-app-secret');
    config()->set('whatsapper.debug.enabled', true);
    Event::fake([WhatsappWebhookReceived::class, WhatsappMessageReceived::class]);
    Log::shouldReceive('debug')->never();
    $headers = $signature === null ? [] : ['X-Hub-Signature-256' => $signature];
    $this->postJson(route('whatsapper.webhook.handle'), ['entry' => []], $headers)->assertForbidden();
    Event::assertNothingDispatched();
    expect(Storage::disk('webhook-security')->allFiles())->toBe([]);
    MockClient::getGlobal()->assertNothingSent();
})->with([null, '', 'sha256=invalid', 'sha1='.str_repeat('0', 40), 'sha256='.str_repeat('0', 64)]);

it('checks the original body rather than decoded and reencoded JSON', function () {
    Whatsapper::enableWebhook('verification-token', 'test-app-secret');
    Event::fake([WhatsappWebhookReceived::class]);
    $body = "{\n  \"entry\": []\n}";
    $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, 'test-app-secret')];
    $this->call('POST', route('whatsapper.webhook.handle'), [], [], [], $headers, $body)->assertOk();
    Event::assertDispatched(WhatsappWebhookReceived::class);
    $this->call('POST', route('whatsapper.webhook.handle'), [], [], [], $headers, '{"entry":[]}')->assertForbidden();
});

it('does not accept the verification token as the signing secret', function () {
    Whatsapper::enableWebhook('verification-token', 'test-app-secret');
    $body = '{"entry":[]}';
    expect(Whatsapper::verifyWebhookSignature($body, 'sha256='.hash_hmac('sha256', $body, 'verification-token')))->toBeFalse();
    $this->get(route('whatsapper.webhook.verify', ['hub.mode' => 'subscribe', 'hub.verify_token' => 'verification-token', 'hub.challenge' => '1234']))
        ->assertOk()->assertContent('1234');
});

it('keeps disabled webhooks unavailable', function () {
    $this->postJson(route('whatsapper.webhook.handle'), [])->assertNotFound();
});

it('keeps payload identifiers inside the configured directory', function () {
    Whatsapper::enableWebhook('verification-token', 'test-app-secret');
    $body = json_encode(['entry' => [['changes' => [['field' => 'messages', 'value' => ['messages' => [[
        'id' => '../../escape', 'type' => 'text', 'text' => ['body' => 'hello'],
        'from' => '390000000000', 'from_user_id' => 'test-user', 'timestamp' => '1795521600',
    ]]]]]]]]);
    $this->call('POST', route('whatsapper.webhook.handle'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, 'test-app-secret'),
    ], $body)->assertOk();
    Storage::disk('webhook-security')->assertExists('whatsapp/payloads/message_..%2F..%2Fescape.json');
    expect(Storage::disk('webhook-security')->allFiles())->toHaveCount(1);
});

it('keeps status identifiers inside the payload directory', function () {
    Whatsapper::enableWebhook('verification-token', 'test-app-secret');
    $body = json_encode(['entry' => [['changes' => [['field' => 'messages', 'value' => ['statuses' => [[
        'id' => '../../escape', 'status' => 'sent', 'timestamp' => '1795521600',
        'recipient_id' => '390000000000', 'recipient_user_id' => 'test-user',
    ]]]]]]]]);
    $this->call('POST', route('whatsapper.webhook.handle'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $body, 'test-app-secret'),
    ], $body)->assertOk();
    Storage::disk('webhook-security')->assertExists('whatsapp/payloads/status_..%2F..%2Fescape_1795521600.json');
    expect(Storage::disk('webhook-security')->allFiles())->toHaveCount(1);
});
