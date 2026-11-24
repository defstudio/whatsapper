<?php

/** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Dto\TextMessage;
use DefStudio\Whatsapper\Exceptions\WhatsapperConfigurationException;
use DefStudio\Whatsapper\Exceptions\WhatsapperMediaException;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\GetMediaInfoRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\GetMediaRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\GetTemplateRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\SendWhatsappMessageRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\WhatsappConnector;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Saloon\Http\Response;

class Whatsapper
{
    protected ?string $whatsappBusinessAccountId = null;

    protected ?string $phoneId = null;

    protected ?string $phoneToken = null;

    protected bool $webhookEnabled = false;

    protected ?string $webhookVerificationToken = null;

    protected ?string $appSecret = null;

    public function enableSending(string $whatsappBusinessAccountId, string $phoneId, string $phoneToken): static
    {
        $this->whatsappBusinessAccountId = $whatsappBusinessAccountId;
        $this->phoneId = $phoneId;
        $this->phoneToken = $phoneToken;

        return $this;
    }

    public function enableWebhook(
        string $verificationToken,
        ?string $appSecret = null,
    ): static {
        $appSecret ??= config('whatsapper.webhook.app_secret');

        if ($appSecret === null || $appSecret === '') {
            throw WhatsapperConfigurationException::appSecretNotConfigured();
        }

        $this->webhookEnabled = true;
        $this->webhookVerificationToken = $verificationToken;
        $this->appSecret = $appSecret;

        return $this;
    }

    public function send(string $to, WhatsappMessage|string $message): Response
    {
        if (is_string($message)) {
            $message = new TextMessage($message);
        }

        return $this->connector()
            ->send(SendWhatsappMessageRequest::make($this->phoneId, $message)->to($to));
    }

    public function getTemplate(string $name, ?string $language = null): Response
    {
        return $this->connector()
            ->send(GetTemplateRequest::make($this->whatsappBusinessAccountId, $name, $language));
    }

    public function getMedia(string $id, ?string $url = null): Response
    {
        $response = $this->connector()
            ->send(new GetMediaInfoRequest($id));

        if (! $response->successful()) {
            return $response;
        }

        $mediaUrl = $response->json('url');

        if (! is_string($mediaUrl)) {
            throw WhatsapperMediaException::invalidDownloadUrl();
        }

        return $this->connector()->send(new GetMediaRequest($mediaUrl));
    }

    public function connector(): WhatsappConnector
    {
        if (! $this->isSendingEnabled()) {
            throw WhatsapperConfigurationException::sendingNotConfigured();
        }

        return WhatsappConnector::make($this->phoneToken);
    }

    public function isWebhookEnabled(): bool
    {
        return $this->webhookEnabled;
    }

    public function isSendingEnabled(): bool
    {
        if ($this->phoneId === null) {
            return false;
        }

        if ($this->phoneToken === null) {
            return false;
        }

        return true;
    }

    public function verifyWebhook(string $token): bool
    {
        if (! $this->isWebhookEnabled()) {
            return false;
        }

        return $token === $this->webhookVerificationToken;
    }

    public function verifyWebhookSignature(string $body, ?string $signature): bool
    {
        if (! $this->webhookEnabled || $this->appSecret === null || $this->appSecret === '' || $signature === null) {
            return false;
        }

        return hash_equals('sha256='.hash_hmac('sha256', $body, $this->appSecret), $signature);
    }

    public function generateWebhookVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function payloadsDisk(): Filesystem
    {
        return Storage::disk(config('whatsapper.webhook.payloads.disk'));
    }

    public function payloadsPath(): string
    {
        return config('whatsapper.webhook.payloads.path', 'whatsapp/payloads');
    }

    public function mediaDisk(): Filesystem
    {
        return Storage::disk(config('whatsapper.webhook.media.disk'));
    }

    public function mediaPath(): string
    {
        return config('whatsapper.webhook.media.path', 'whatsapp/media');
    }

    public function shouldStoreImages(): bool
    {
        return config('whatsapper.webhook.media.images.store');
    }

    public function shouldStoreVideos(): bool
    {
        return config('whatsapper.webhook.media.videos.store');
    }

    public function shouldStoreAudio(): bool
    {
        return config('whatsapper.webhook.media.audio.store');
    }

    public function shouldStoreMessages(): bool
    {
        return config('whatsapper.webhook.payloads.messages.store');
    }

    public function shouldStoreStatusChanges(): bool
    {
        return config('whatsapper.webhook.payloads.status_changes.store');
    }
}
