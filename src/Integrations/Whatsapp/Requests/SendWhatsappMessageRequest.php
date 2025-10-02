<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp\Requests;

use DefStudio\Whatsapper\Concerns\WhatsappMessage;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SendWhatsappMessageRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    protected string $to;
    protected WhatsappMessage $message;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/messages';
    }

    public function __construct(WhatsappMessage $message)
    {
        $this->message = $message;
    }

    public function to(string $to): static
    {
        $this->to = $to;
        return $this;
    }

    protected function defaultBody(): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => $this->to,
            ...$this->message->toRequestBody(),
        ];
    }
}
