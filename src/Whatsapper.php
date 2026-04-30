<?php

/** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
use DefStudio\Whatsapper\Contracts\WhatsappWebhookController;
use DefStudio\Whatsapper\Exceptions\WhatsapperConfigurationException;
use DefStudio\Whatsapper\Http\Controllers\WhatsapperWebhookController;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\SendWhatsappMessageRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\WhatsappConnector;
use Saloon\Http\Response;

class Whatsapper
{
    protected ?string $phoneId;

    protected ?string $phoneToken;

    protected bool $webhookEnabled = false;

    protected array $webhookMiddleware = [];

    protected ?string $webhookPath = null;

    protected ?string $webhookControllerClass = null;

    protected ?string $webhookVerificationToken = null;

    public function enableSending(string $phoneId, string $phoneToken): static
    {
        $this->phoneId = $phoneId;
        $this->phoneToken = $phoneToken;

        return $this;
    }

    public function enableWebhook(
        string $verificationToken,
        string $path = '/webhooks/whatsapp',
        array $middleware = [],
        ?string $controllerClass = null
    ): static {
        $this->webhookEnabled = true;
        $this->webhookVerificationToken = $verificationToken;
        $this->webhookMiddleware = $middleware;
        $this->webhookPath = $path;
        $this->webhookControllerClass = $controllerClass ?? WhatsapperWebhookController::class;

        if (! is_subclass_of($this->webhookControllerClass, WhatsappWebhookController::class)) {
            throw WhatsapperConfigurationException::webhookControllerMustImplementContract();
        }

        return $this;
    }

    public function send(string $to, WhatsappMessage $message): Response
    {
        if (! $this->isSendingEnabled()) {
            throw WhatsapperConfigurationException::sendingNotConfigured();
        }

        return WhatsappConnector::make($this->phoneId, $this->phoneToken)
            ->send(SendWhatsappMessageRequest::make($message)->to($to));
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

    public function getWebhookMiddleware(): array
    {
        return $this->webhookMiddleware;
    }

    public function getWebhookPath(): string
    {
        return $this->webhookPath;
    }

    public function getWebhookControllerClass(): string
    {
        return $this->webhookControllerClass;
    }

    public function verifyWebhook(string $token): bool
    {
        if (! $this->isWebhookEnabled()) {
            return false;
        }

        return $token === $this->webhookVerificationToken;
    }
}
