<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessageStatusChange;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessageStatusChange;

class MessageFailedStatus implements WhatsappMessageStatusChange
{
    use IsWhatsappMessageStatusChange;

    public function __construct(
        protected array $errors
    ) {}

    public static function build(array $data): static
    {
        return new static($data['errors'] ?? [])
            ->fillStatusData($data);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
