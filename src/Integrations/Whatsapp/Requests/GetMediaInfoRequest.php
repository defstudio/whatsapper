<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMediaInfoRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(
        protected string $mediaId,
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "https://graph.facebook.com/v25.0/$this->mediaId";
    }
}
