<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTemplateRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(
        protected string $businessAccountId,
        protected string $name,
        protected ?string $language = null,
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "$this->businessAccountId/message_templates";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'name' => $this->name,
            'language' => $this->language,
        ], static fn (mixed $value) => $value !== null);
    }
}
