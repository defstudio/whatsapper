<?php

namespace DefStudio\Whatsapper\Dto\Concerns;

use Carbon\Carbon;
use DefStudio\Whatsapper\Dto\WhatsappContact;
use DefStudio\Whatsapper\Dto\WhatsappMessageContext;

trait IsWhatsappMessageStatusChange
{
    protected Carbon $timestamp;

    protected string $type;

    protected ?WhatsappMessageContext $context;

    protected WhatsappContact $contact;

    public function fillStatusData(array $status): static
    {
        $this->type = $status['status'];
        $this->timestamp = Carbon::createFromTimestamp($status['timestamp']);
        $this->context = new WhatsappMessageContext($status['recipient_id'], $status['id']);
        $this->contact = new WhatsappContact($status['recipient_user_id'], $status['recipient_id']);

        return $this;
    }

    public function timestamp(): Carbon
    {
        return $this->timestamp;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function context(): ?WhatsappMessageContext
    {
        return $this->context;
    }

    public function from(): WhatsappContact
    {
        return $this->contact;
    }
}
