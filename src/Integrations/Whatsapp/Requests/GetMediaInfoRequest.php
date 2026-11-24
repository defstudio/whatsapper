<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp\Requests;

use DefStudio\Whatsapper\Support\MediaSafety;
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
    ) {
        MediaSafety::validateId($mediaId);
    }

    protected function defaultConfig(): array
    {
        return ['allow_redirects' => false];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "https://graph.facebook.com/v25.0/$this->mediaId";
    }
}
