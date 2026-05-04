<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class WhatsappConnector extends Connector
{
    use AcceptsJson;

    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
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
        return new TokenAuthenticator($this->token);
    }
}
