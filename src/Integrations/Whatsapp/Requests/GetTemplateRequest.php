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
        protected string $name,
        protected ?string $language = null,
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/message_template_library';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'name' => $this->name,
            'language' => $this->language,
        ], static fn (mixed $value) => $value !== null);
    }
}
