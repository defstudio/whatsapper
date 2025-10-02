<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper;

use DefStudio\Whatsapper\Concerns\WhatsappMessage;
use DefStudio\Whatsapper\Integrations\Whatsapp\Requests\SendWhatsappMessageRequest;
use DefStudio\Whatsapper\Integrations\Whatsapp\WhatsappConnector;
use Saloon\Http\Response;
use Saloon\Traits\Makeable;

class Whatsapp
{
    use Makeable;


    protected string $phoneId;
    protected string $phoneToken;

    public function configure(string $phoneId, string $phoneToken): static
    {
        $this->phoneId = $phoneId;
        $this->phoneToken = $phoneToken;

        return $this;
    }

    public function send(string $to, WhatsappMessage $message): Response
    {
        return WhatsappConnector::make($this->phoneId, $this->phoneToken)
            ->send(SendWhatsappMessageRequest::make($message)->to($to));
    }
}
