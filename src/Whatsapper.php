<?php

/** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Exceptions\WhatsapperConfigurationException;
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
    protected ?string $whatsappBusinessAccountId;

    protected ?string $phoneId;

    protected ?string $phoneToken;

    protected bool $webhookEnabled = false;

    protected ?string $webhookVerificationToken = null;

    public function enableSending(string $whatsappBusinessAccountId, string $phoneId, string $phoneToken): static
    {
        $this->whatsappBusinessAccountId = $whatsappBusinessAccountId;
        $this->phoneId = $phoneId;
        $this->phoneToken = $phoneToken;

        return $this;
    }

    public function enableWebhook(
        string $verificationToken,
    ): static {
        $this->webhookEnabled = true;
        $this->webhookVerificationToken = $verificationToken;

        return $this;
    }

    public function send(string $to, WhatsappMessage $message): Response
    {
        return $this->connector()
            ->send(SendWhatsappMessageRequest::make($this->phoneId, $message)->to($to));
    }

    public function getTemplate(string $name, ?string $language = null): Response
    {
        return $this->connector()
            ->send(GetTemplateRequest::make($this->whatsappBusinessAccountId, $name, $language));
    }

    public function getMedia(string $id, string $url): Response
    {
        $response = $this->connector()
            ->send(new GetMediaRequest($url));

        if ($response->successful()) {
            return $response;
        }

        $response = $this->connector()
            ->send(new GetMediaInfoRequest($id));

        if (! $response->successful()) {
            return $response;
        }

        return $this->connector()
            ->send(new GetMediaRequest($response->json('url')));
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
        return Storage::disk(config('whatsapper.webhook.images.disk'));
    }

    public function mediaPath(): string
    {
        return config('whatsapper.webhook.images.path', 'whatsapp/media');
    }
}
