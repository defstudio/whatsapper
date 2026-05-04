<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Whatsapper\Dto\Concerns;

use Carbon\Carbon;
use DefStudio\Whatsapper\Dto\WhatsappContact;
use DefStudio\Whatsapper\Dto\WhatsappMessageContext;
use DefStudio\Whatsapper\Exceptions\WhatsapperParserException;

trait IsWhatsappMessage
{
    protected string $type;

    protected string $id;

    protected Carbon $timestamp;

    protected ?WhatsappMessageContext $context;

    protected WhatsappContact $contact;

    public function fillMessageData(array $message): static
    {
        $this->type = $message['type'];
        $this->id = $message['id'];

        $this->fillContact([
            'user_id' => $message['from_user_id'],
            'wa_id' => $message['from'],
        ]);

        $this->timestamp = Carbon::createFromTimestamp($message['timestamp']);
        $this->context = ! empty($message['context']) ? new WhatsappMessageContext($message['context']['from'], $message['context']['id']) : null;

        return $this;
    }

    public function fillContact(array $data): static
    {
        $this->contact = new WhatsappContact(
            $data['user_id'] ?? $this->contact->userId ?? throw WhatsapperParserException::missingUserId(),
            $data['wa_id'] ?? $this->contact->phoneNumber ?? throw WhatsapperParserException::missingPhoneNumber(),
            $data['profile']['name'] ?? null,
        );

        return $this;
    }

    public function context(): ?WhatsappMessageContext
    {
        return $this->context;
    }

    public function from(): WhatsappContact
    {
        return $this->contact;
    }

    public function id(): string
    {
        return $this->id;
    }
}
