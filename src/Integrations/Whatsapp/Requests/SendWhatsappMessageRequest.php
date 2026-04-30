<?php

namespace DefStudio\Whatsapper\Integrations\Whatsapp\Requests;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;
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

    protected string $phoneId;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "/$this->phoneId/messages";
    }

    public function __construct(string $phoneId, WhatsappMessage $message)
    {
        $this->message = $message;
        $this->phoneId = $phoneId;
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
