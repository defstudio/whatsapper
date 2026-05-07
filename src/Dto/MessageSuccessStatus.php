<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessageStatusChange;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessageStatusChange;

abstract class MessageSuccessStatus implements WhatsappMessageStatusChange
{
    use IsWhatsappMessageStatusChange;

    public function __construct(
        protected array $pricing
    ) {}

    public static function build(array $data): static
    {
        return new static($data['pricing'] ?? [])
            ->fillStatusData($data);
    }

    public function free(): bool
    {
        return !$this->pricing['billable'];
    }
}
