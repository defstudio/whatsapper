<?php

use DefStudio\Whatsapper\Dto\ImageMessage;
use DefStudio\Whatsapper\Events\WhatsappMessageReceived;
use DefStudio\Whatsapper\Exceptions\WhatsapperMediaException;
use DefStudio\Whatsapper\Facades\Whatsapper;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\GetMediaInfoRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\GetMediaRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\WhatsappConnector;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    MockClient::destroyGlobal();
    Storage::fake('media-security');
    config()->set('whatsapper.webhook.media.disk', 'media-security');
    Whatsapper::enableSending('test-account', 'test-phone', 'test-access-token');
});

afterEach(fn () => MockClient::destroyGlobal());

it('resolves media through Graph and ignores attacker supplied URLs', function (string $type, string $mime, string $extension, bool $includeUrl) {
    $mock = MockClient::global([
        GetMediaInfoRequest::class => MockResponse::make(['url' => 'https://lookaside.fbsbx.com/whatsapp_business/attachments/test']),
        GetMediaRequest::class => MockResponse::make('test-media-content'),
    ]);
    $media = ['id' => '123456789', 'mime_type' => $mime];
    if ($includeUrl) {
        $media['url'] = 'http://127.0.0.1/steal';
    }
    $event = new WhatsappMessageReceived([
        'type' => $type, 'id' => 'wamid.test', 'from' => '390000000000',
        'from_user_id' => 'test-user', 'timestamp' => '1795521600', $type => $media,
    ], null, null, [], []);
    Storage::disk('media-security')->assertExists("whatsapp/media/123456789.$extension");
    expect(file_get_contents($event->message->path()))->toBe('test-media-content');
    $mock->assertSentInOrder([
        fn ($request) => $request->resolveEndpoint() === 'https://graph.facebook.com/v25.0/123456789',
        fn ($request, $response) => $request->resolveEndpoint() === 'https://lookaside.fbsbx.com/whatsapp_business/attachments/test'
            && $response->getPendingRequest()->headers()->get('Authorization') === 'Bearer test-access-token',
    ]);
    $mock->assertSentCount(2);
})->with([
    ['image', 'image/jpeg', 'jpg'], ['video', 'video/mp4', 'mp4'], ['audio', 'audio/ogg', 'ogg'],
    ['image', 'application/x-httpd-php', 'bin'], ['image', 'image/svg+xml', 'bin'],
])->with([true, false]);

it('rejects untrusted download destinations even when returned by Graph', function (string $url) {
    $mock = MockClient::global([GetMediaInfoRequest::class => MockResponse::make(['url' => $url])]);
    expect(fn () => Whatsapper::getMedia('123456789', 'https://attacker.invalid/steal'))->toThrow(WhatsapperMediaException::class);
    $mock->assertSentCount(1);
    expect(fn () => new GetMediaRequest($url))->toThrow(WhatsapperMediaException::class);
})->with([
    'http://lookaside.fbsbx.com/file', 'https://attacker.invalid/file', 'http://127.0.0.1/file',
    'https://127.0.0.1/file', 'https://facebook.com.attacker.invalid/file', 'https://evilfacebook.com/file',
    'https://facebook.com@attacker.invalid/file', 'https://user:password@lookaside.fbsbx.com/file',
    'https://lookaside.fbsbx.com:8443/file', 'https://lookaside.fbsbx.com/file#fragment',
    'file:///etc/passwd', '//lookaside.fbsbx.com/file',
]);

it('rejects unsafe media identifiers before any request or filesystem write', function (string $id) {
    $mock = MockClient::global([]);
    expect(fn () => ImageMessage::build(['image' => ['id' => $id, 'mime_type' => 'image/jpeg']]))
        ->toThrow(WhatsapperMediaException::class);
    expect(fn () => new GetMediaInfoRequest($id))->toThrow(WhatsapperMediaException::class);
    $mock->assertNothingSent();
    expect(Storage::disk('media-security')->allFiles())->toBe([]);
})->with(['../escape', '..\\escape', '/absolute', '', '..', '.', '123?access_token=evil', '123/../../me', "123\0", str_repeat('1', 201)]);

it('does not download when Graph fails or returns an invalid response', function (array $body, int $status) {
    $mock = MockClient::global([GetMediaInfoRequest::class => MockResponse::make($body, $status)]);
    if ($status === 200) {
        expect(fn () => Whatsapper::getMedia('123456789'))->toThrow(WhatsapperMediaException::class);
    } else {
        expect(Whatsapper::getMedia('123456789')->status())->toBe($status);
    }
    $mock->assertSentCount(1);
})->with([[[], 200], [['url' => ['invalid']], 200], [[], 404], [[], 302]]);

it('does not follow HTTP redirects for metadata or media downloads', function (bool $metadata) {
    $history = [];
    $connector = new WhatsappConnector('test-access-token');
    $sender = $connector->sender();
    $stack = $sender->getHandlerStack();
    $stack->setHandler(new MockHandler([
        new Response(302, ['Location' => 'https://attacker.invalid/steal']),
        new Response(200, [], 'should-not-be-requested'),
    ]));
    $stack->push(Middleware::history($history));
    $response = $connector->send($metadata ? new GetMediaInfoRequest('123456789') : new GetMediaRequest('https://lookaside.fbsbx.com/file'));
    expect($response->status())->toBe(302)->and($history)->toHaveCount(1);
    expect($history[0]['request']->getHeaderLine('Authorization'))->toBe('Bearer test-access-token');
})->with([true, false]);

it('exercises the webhook route with the reported forged media payload', function (string $type, bool $signed) {
    config()->set('whatsapper.webhook.payloads.disk', 'media-security');
    Whatsapper::enableWebhook('test-verification-token', 'test-app-secret');
    $mock = MockClient::global([
        GetMediaInfoRequest::class => MockResponse::make(['url' => 'https://lookaside.fbsbx.com/file']),
        GetMediaRequest::class => MockResponse::make('inert-media-content'),
    ]);
    $body = json_encode(['entry' => [['changes' => [['field' => 'messages', 'value' => ['messages' => [[
        'type' => $type, 'id' => 'wamid.test', 'from' => '390000000000',
        'from_user_id' => 'test-user', 'timestamp' => '1795521600',
        $type => ['id' => '123456789', 'url' => 'http://127.0.0.1/steal', 'mime_type' => 'application/x-httpd-php'],
    ]]]]]]]]);
    $headers = ['CONTENT_TYPE' => 'application/json'];
    if ($signed) {
        $headers['HTTP_X_HUB_SIGNATURE_256'] = 'sha256='.hash_hmac('sha256', $body, 'test-app-secret');
    }
    $response = $this->call('POST', route('whatsapper.webhook.handle'), [], [], [], $headers, $body);
    if ($signed) {
        $response->assertOk();
        $mock->assertSentCount(2);
        Storage::disk('media-security')->assertExists('whatsapp/media/123456789.bin');
        Storage::disk('media-security')->assertMissing('whatsapp/media/123456789.php');
    } else {
        $response->assertForbidden();
        $mock->assertNothingSent();
        expect(Storage::disk('media-security')->allFiles())->toBe([]);
    }
})->with(['image', 'video', 'audio'])->with([true, false]);

it('does not store failed media downloads', function () {
    MockClient::global([
        GetMediaInfoRequest::class => MockResponse::make(['url' => 'https://lookaside.fbsbx.com/file']),
        GetMediaRequest::class => MockResponse::make('unavailable', 503),
    ]);
    expect(fn () => ImageMessage::build(['image' => ['id' => '123456789', 'mime_type' => 'image/jpeg']]))
        ->toThrow(WhatsapperMediaException::class);
    expect(Storage::disk('media-security')->allFiles())->toBe([]);
});

it('uses trusted downloads for explicit storage when automatic storage is disabled', function () {
    config()->set('whatsapper.webhook.media.images.store', false);
    $mock = MockClient::global([
        GetMediaInfoRequest::class => MockResponse::make(['url' => 'https://lookaside.fbsbx.com/file']),
        GetMediaRequest::class => MockResponse::make('test-media-content'),
    ]);
    $event = new WhatsappMessageReceived([
        'type' => 'image', 'id' => 'wamid.test', 'from' => '390000000000',
        'from_user_id' => 'test-user', 'timestamp' => '1795521600',
        'image' => ['id' => '123456789', 'mime_type' => 'image/jpeg', 'url' => 'https://attacker.invalid/steal'],
    ], null, null, [], []);
    $mock->assertNothingSent();
    $path = Storage::disk('media-security')->path('explicit.jpg');
    expect($event->message->store($path))->toBe($path);
    Storage::disk('media-security')->assertExists('explicit.jpg');
    $mock->assertSentCount(2);
});
