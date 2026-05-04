<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMediaRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(
        protected string $url,
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return $this->url;
    }
}
