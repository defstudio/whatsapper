# Whatsapper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/defstudio/whatsapper.svg?style=flat-square)](https://packagist.org/packages/defstudio/whatsapper)
[![Tests](https://img.shields.io/github/actions/workflow/status/defstudio/whatsapper/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/defstudio/whatsapper/actions/workflows/run-tests.yml)
[![Code Style](https://img.shields.io/github/actions/workflow/status/defstudio/whatsapper/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/defstudio/whatsapper/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/defstudio/whatsapper.svg?style=flat-square)](https://packagist.org/packages/defstudio/whatsapper)

Whatsapper connects Laravel applications to the WhatsApp Business Cloud API. It sends text and template messages, receives signed webhooks, dispatches Laravel events with typed message and status objects, and downloads incoming images, videos, and audio through Laravel's filesystem.

```php
use DefStudio\Whatsapper\Facades\Whatsapper;

// After configuring sending credentials in your service provider:
$response = Whatsapper::send($recipientPhoneNumber, 'Your order is ready.');
```

The built-in Graph API requests currently target `v25.0`. The package provides a facade, DTOs, HTTP requests, and webhook routes; it does not provide database models, migrations, views, a dashboard, or a notification channel.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configure credentials](#configure-credentials)
- [Send messages](#send-messages)
- [Receive webhooks](#receive-webhooks)
- [Handle incoming messages](#handle-incoming-messages)
- [Handle message statuses](#handle-message-statuses)
- [Download and store media](#download-and-store-media)
- [Configuration reference](#configuration-reference)
- [Webhook security and upgrading](#webhook-security-and-upgrading)
- [Development](#development)

## Requirements

The package declares support for PHP `^8.4` and Laravel components `^11.0`, `^12.0`, or `^13.0` in [composer.json](composer.json). It uses Saloon `^4.1` for HTTP requests.

Your application supplies the WhatsApp Business Account ID, phone number ID, and access token for sending and media retrieval. Receiving webhooks additionally requires a verification token of your choice and the Meta application's App Secret.

## Installation

```bash
composer require defstudio/whatsapper
```

Laravel automatically discovers the service provider and facade. Publish the configuration when you need to change webhook routes, middleware, storage, or debug settings:

```bash
php artisan vendor:publish --tag="whatsapper-config"
```

The configuration is published to `config/whatsapper.php`. No migrations or views need to be published.

## Configure credentials

Keep credentials in your application's configuration. For example, add this entry to `config/services.php`:

```php
'whatsapp' => [
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'webhook_verification_token' => env('WHATSAPP_WEBHOOK_VERIFICATION_TOKEN'),
],
```

These `WHATSAPP_*` names are application-defined variables used by this example; the package does not read them automatically. The package reads `WHATSAPPER_APP_SECRET` through its own configuration.

Set the values in your environment, then enable the capabilities you need in your application's `AppServiceProvider::boot()`:

```php
use DefStudio\Whatsapper\Facades\Whatsapper;

public function boot(): void
{
    Whatsapper::enableSending(
        whatsappBusinessAccountId: config('services.whatsapp.business_account_id'),
        phoneId: config('services.whatsapp.phone_number_id'),
        phoneToken: config('services.whatsapp.access_token'),
    );

    Whatsapper::enableWebhook(
        verificationToken: config('services.whatsapp.webhook_verification_token'),
    );
}
```

Sending and webhooks are enabled separately. Omit `enableWebhook()` for an application that only sends messages. If you receive media with automatic storage enabled, you also need `enableSending()` because media downloads use the same access token.

`enableWebhook()` reads the App Secret from `whatsapper.webhook.app_secret`, populated by `WHATSAPPER_APP_SECRET`. You can also pass it explicitly:

```php
Whatsapper::enableWebhook($verificationToken, appSecret: $appSecret);
```

`Whatsapper::generateWebhookVerificationToken()` returns a random 64-character hexadecimal token. Generate it once, save it in your application's configuration, and use the same value when configuring the webhook subscription. Do not generate a new token on every request.

## Send messages

### Text

Pass a string or a `TextMessage` instance:

```php
use DefStudio\Whatsapper\Dto\TextMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;

$response = Whatsapper::send($recipientPhoneNumber, 'Your order is ready.');

$response = Whatsapper::send(
    $recipientPhoneNumber,
    new TextMessage('Your order is ready.'),
);
```

`send()` returns a `Saloon\Http\Response`. Inspect it to handle API errors or obtain the message ID:

```php
if ($response->successful()) {
    $messageId = $response->json('messages.0.id');
} else {
    $error = $response->json('error');
    // Handle the API error in your application.
}
```

Requests run synchronously. The package does not queue sends or persist outgoing messages; your application owns those workflows.

### Templates

Create a `TemplateMessage` using the template name and language code:

```php
use DefStudio\Whatsapper\Dto\TemplateMessage;
use DefStudio\Whatsapper\Facades\Whatsapper;

$message = new TemplateMessage('order_ready', 'en_US')
    ->withBodyParameters(['Alex', 'ORDER-123'])
    ->withButtonParameters(['ORDER-123']);

$response = Whatsapper::send($recipientPhoneNumber, $message);
```

Scalar parameters become text parameters. Associative keys become `parameter_name` values for named parameters:

```php
$message->withBodyParameters([
    'customer_name' => 'Alex',
    'order_number' => 'ORDER-123',
]);
```

You can also pass complete parameter arrays. Button entries accept a `sub_type`, an `index`, and a `parameters` array:

```php
$message->withButtonParameters([
    [
        'sub_type' => 'url',
        'index' => 0,
        'parameters' => [
            ['type' => 'text', 'text' => 'ORDER-123'],
        ],
    ],
]);
```

Simple button values default to URL buttons, with their array index used as the button index. `withBottomButtonsParameters()` is an alias of `withButtonParameters()`.

Fetch a template's API response with:

```php
$response = Whatsapper::getTemplate('order_ready', 'en_US');
$templates = $response->json('data', []);
```

`$message->text()` also fetches the template and returns the first matching template's `BODY` text, replacing positional placeholders such as `{{1}}` with body parameters. It performs an HTTP request; it is not a local renderer for all template components or named placeholders.

### Other outgoing payloads

`send()` accepts any implementation of `DefStudio\Whatsapper\Contracts\WhatsappMessage` and serializes its `toRequestBody()` result. The package includes outgoing serialization for text, templates, and `ButtonMessage`; it does not validate whether Meta accepts a particular payload.

Incoming image, video, audio, and unsupported-message DTOs do not implement outgoing serialization. `ReactionMessage::toRequestBody()` currently produces a text payload containing the emoji, rather than an API reaction payload. Use a custom message implementation for outgoing formats the package does not provide.

## Receive webhooks

The service provider registers these routes automatically:

| Method | Default path | Route name | Purpose |
| --- | --- | --- | --- |
| GET | `/whatsapp/webhook` | `whatsapper.webhook.verify` | Subscription verification |
| POST | `/whatsapp/webhook` | `whatsapper.webhook.handle` | Incoming webhook processing |

Both routes use the configured `webhook.middleware`, which defaults to `['api']`. The default path has no automatic `/api` prefix. Set `webhook.path` to change it.

Configure your webhook callback to use this URL on your application's public HTTPS domain and the verification token passed to `enableWebhook()`. Both routes return HTTP 404 until webhooks are enabled.

The GET route accepts a `subscribe` handshake and returns the supplied challenge when the verification token matches. An invalid mode returns HTTP 400; an invalid token returns HTTP 403.

The POST route validates `X-Hub-Signature-256` against the original request body before logging, dispatching events, saving payloads, or downloading media. Missing or invalid signatures return HTTP 403. Successful processing returns HTTP 200 with an empty body.

### Webhook events

| Event | Available data |
| --- | --- |
| `WhatsappWebhookReceived` | `$payload`: a `WhatsappWebhookPayload` wrapping the entire request payload |
| `WhatsappMessageReceived` | `$message`, `$messageData`, `$metadata`, `$contacts`, `$rawChange`, `$rawPayload` |
| `WhatsappMessageStatusUpdated` | `$statusChange`, `$status`, `$metadata`, `$rawChange`, `$rawPayload` |
| `WhatsappOtherWebhookEventReceived` | `$field`, `$value`, `$rawChange`, `$rawPayload` |

All event classes are in `DefStudio\Whatsapper\Events`. The full-payload event is dispatched first, followed by individual message events, status events, and other change events. Entries and changes are collected across the entire payload.

`WhatsappWebhookPayload` exposes `messages()`, `statuses()`, and `otherChanges()`. The last method returns changes whose `field` is not `messages`.

### Custom controller

Set `whatsapper.webhook.controller` to your controller class to replace the default handler. It must expose `verify(Request $request): Response` and `handle(Request $request): Response`; the package provides a matching `WhatsappWebhookController` contract.

For payload normalization, you can extend `DefStudio\Whatsapper\Http\Controllers\WhatsapperWebhookController`. Preserve signature validation against the original request body before processing any payload or causing side effects.

## Handle incoming messages

Register listeners using your application's normal Laravel event setup. For example, inside a service provider's `boot()` method:

```php
use DefStudio\Whatsapper\Dto\TextMessage;
use DefStudio\Whatsapper\Events\WhatsappMessageReceived;
use Illuminate\Support\Facades\Event;

Event::listen(WhatsappMessageReceived::class, function (WhatsappMessageReceived $event): void {
    $message = $event->message;

    if ($message instanceof TextMessage) {
        $sender = $message->from()->phoneNumber;
        $text = $message->text();
        // Pass the message to your application workflow.
    }
});
```

The message event maps incoming types to these DTOs:

| Incoming type | DTO | Additional accessors |
| --- | --- | --- |
| `text` | `TextMessage` | `text()` |
| `button` | `ButtonMessage` | `text()`, `payload()` |
| `image` | `ImageMessage` | `path()`, `store($path)`, `extension()` |
| `video` | `VideoMessage` | `path()`, `store($path)`, `extension()` |
| `audio` | `AudioMessage` | `path()`, `store($path)`, `extension()`, `isVoice()` |
| `reaction` | `ReactionMessage` | `text()` returns the emoji; `context()` identifies the target message |
| Any other type | `UnsupportedMessage` | Common message accessors |

All DTOs above are in `DefStudio\Whatsapper\Dto`. Received messages expose `id()`, `type()`, `timestamp()` (a Carbon instance), `from()`, `context()`, and `text()`.

`from()` returns a `WhatsappContact` with public `userId`, `phoneNumber`, and nullable `name` properties. Reply context is a nullable `WhatsappMessageContext` with public `from` and `messageId` properties. Forwarding metadata alone does not create a reply context: both `context.from` and `context.id` must be present.

**Payload compatibility:** the current message parser requires `from_user_id` as well as `from`, and the status parser requires `recipient_user_id` as well as `recipient_id`. Payloads containing only phone number identifiers need application-level normalization before constructing the typed events. The parser does not derive missing user IDs from phone numbers.

Media `text()` returns a `<MEDIA #id>` marker. Unsupported message types retain common metadata but do not expose a parsed type-specific payload; use `$event->messageData` for the original data.

## Handle message statuses

Listen for `DefStudio\Whatsapper\Events\WhatsappMessageStatusUpdated`. Its `$statusChange` property maps to:

| Status | DTO |
| --- | --- |
| `sent` | `MessageSentStatus` |
| `delivered` | `MessageDeliveredStatus` |
| `read` | `MessageReadStatus` |
| `failed` with an error whose code is the string `'131047'` | `OutOfSupportWindowStatus` |
| Other `failed` statuses | `MessageFailedStatus` |
| Any other status | `UnsupportedStatus` |

Status DTOs expose `type()`, `timestamp()`, `from()`, and `context()`. For a status, `from()` contains the recipient identifiers and `context()->messageId` contains the affected message ID.

`MessageFailedStatus` and its `OutOfSupportWindowStatus` subclass expose `errors()`. Successful status DTOs expose `free()`, which reads the payload's `pricing.billable` flag and assumes that flag is present. The special support-window mapping compares the error code strictly as a string. The original status array remains available as `$event->status`.

## Download and store media

### Automatic storage

Incoming images, videos, and audio are downloaded while their message DTO is being constructed, before the individual `WhatsappMessageReceived` event is dispatched. Automatic storage is enabled by default for all three types.

Files are written to the configured media disk under:

```text
whatsapp/media/{media-id}.{extension}
```

Use `$message->path()` to retrieve the disk's path for an automatically stored file. With a local disk, this is a local filesystem path; remote disk paths are not local files or public URLs. If automatic storage is disabled, `path()` throws `WhatsapperMediaException`.

Set any of these environment variables to disable automatic downloads for that type:

```dotenv
WHATSAPPER_STORE_IMAGES=false
WHATSAPPER_STORE_VIDEOS=false
WHATSAPPER_STORE_AUDIO=false
```

Downloads require the credentials configured by `enableSending()`. A failed automatic download or write throws `WhatsapperMediaException` and interrupts webhook processing. Disabling automatic storage lets your application choose when to retrieve media.

### Explicit downloads

```php
use DefStudio\Whatsapper\Facades\Whatsapper;

$response = Whatsapper::getMedia($mediaId);

if ($response->successful()) {
    $contents = $response->body();
}
```

`getMedia()` first resolves the media ID through Graph API metadata, then downloads the returned URL. If the metadata request fails, it returns that response without attempting a download. The optional second argument, `getMedia($id, $url)`, is retained for compatibility and ignored.

For an incoming media DTO, `$message->store($absoluteLocalPath)` downloads the media and writes it to the supplied local filesystem path. Create the destination directory first. This method returns the supplied path, uses a local file write rather than the configured media disk, and does not update the value returned by `path()`.

### Download restrictions

Media IDs must start with an ASCII letter or digit and contain at most 200 characters from letters, digits, dots, underscores, and hyphens. Downloads accept only HTTPS URLs on `facebook.com`, `fbsbx.com`, or `fbcdn.net`, including subdomains. URL credentials, fragments, and ports other than 443 are rejected. Metadata and media requests do not follow redirects; URLs supplied in incoming webhook media objects are ignored.

Automatic filenames use fixed extensions for known image, video, and audio MIME types; other types use `.bin`. This naming rule does not validate file contents. Store received media on a private disk and treat it as untrusted input.

## Configuration reference

See [config/whatsapper.php](config/whatsapper.php) for the complete configuration.

| Configuration key | Environment variable | Default |
| --- | --- | --- |
| `debug.enabled` | `WHATSAPPER_DEBUG` | `false` |
| `webhook.app_secret` | `WHATSAPPER_APP_SECRET` | `null` |
| `webhook.path` | — | `whatsapp/webhook` |
| `webhook.controller` | — | Package webhook controller |
| `webhook.middleware` | — | `['api']` |
| `webhook.payloads.disk` | `WHATSAPPER_PAYLOADS_DISK` | Default Laravel filesystem disk |
| `webhook.payloads.path` | `WHATSAPPER_PAYLOADS_PATH` | `whatsapp/payloads` |
| `webhook.payloads.messages.store` | `WHATSAPPER_STORE_MESSAGES` | `true` |
| `webhook.payloads.status_changes.store` | `WHATSAPPER_STORE_STATUS_CHANGES` | `true` (see below) |
| `webhook.media.disk` | `WHATSAPPER_MEDIA_DISK` | Default Laravel filesystem disk |
| `webhook.media.path` | `WHATSAPPER_MEDIA_PATH` | `whatsapp/media` |
| `webhook.media.images.store` | `WHATSAPPER_STORE_IMAGES` | `true` |
| `webhook.media.videos.store` | `WHATSAPPER_STORE_VIDEOS` | `true` |
| `webhook.media.audio.store` | `WHATSAPPER_STORE_AUDIO` | `true` |

The webhook saves extracted message and status records as JSON, including their associated change data:

```text
whatsapp/payloads/message_{encoded-message-id}.json
whatsapp/payloads/status_{encoded-message-id}_{encoded-timestamp}.json
```

Identifiers in these filenames are encoded with `rawurlencode()`.

**Current storage behavior:** the default controller checks `shouldStoreMessages()` for both messages and statuses. Consequently, `WHATSAPPER_STORE_MESSAGES` controls both kinds of payload files; `WHATSAPPER_STORE_STATUS_CHANGES` is exposed in configuration and through `shouldStoreStatusChanges()`, but is not consulted by the default controller.

When debug mode is enabled, authenticated webhook payloads are written to the application log. Payload files and logs may contain message content and contact information; configure their access and retention in your application.

## Webhook security and upgrading

When upgrading an application with an already published configuration:

1. Add `'app_secret' => env('WHATSAPPER_APP_SECRET')` to the `webhook` section of `config/whatsapper.php`.
2. Set `WHATSAPPER_APP_SECRET` to the Meta application's App Secret, or pass `appSecret` explicitly to `enableWebhook()`.
3. Rebuild Laravel's configuration cache if your deployment uses it.
4. Preserve raw-body signature validation in any custom webhook controller.

The App Secret and the GET subscription verification token serve different purposes. Enabling webhooks without an App Secret throws `WhatsapperConfigurationException`.

Media retrieval now resolves URLs through Graph API and ignores caller-supplied or webhook-supplied download URLs. Applications receiving media with automatic storage enabled must configure sending credentials. Existing stored files are not changed.

Report vulnerabilities privately using the process in [SECURITY.md](SECURITY.md).

## Development

Install development dependencies with Composer, then use the scripts defined by the package:

```bash
composer install
composer test
composer analyse
composer format
```

For a coverage report, run `composer test-coverage` with a PHP coverage driver installed. Tests use Orchestra Testbench and Pest; the suite includes webhook signature, media download safety, and message-context regression tests.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history and upgrade requirements.

## Credits

- [Fabio Ivona](https://github.com/fabio-ivona)
- [All contributors](https://github.com/defstudio/whatsapper/graphs/contributors)

## License

Released under the [MIT License](LICENSE.md).
