<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class WhatsappConnector extends Connector
{
    use AcceptsJson;

    protected string $phoneId;
    protected string $phoneToken;

    public function __construct(string $phoneId, string $phoneToken)
    {
        $this->phoneId = $phoneId;
        $this->phoneToken = $phoneToken;
    }


    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return "https://graph.facebook.com/v22.0/$this->phoneId";
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new TokenAuthenticator($this->phoneToken);
    }


}
