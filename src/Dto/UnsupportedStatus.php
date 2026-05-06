<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessageStatusChange;
use DefStudio\Whatsapper\Dto\Concerns\IsWhatsappMessageStatusChange;

class UnsupportedStatus implements WhatsappMessageStatusChange
{
    use IsWhatsappMessageStatusChange;

    public static function build(array $data): static
    {
        return new static()
            ->fillStatusData($data);
    }
}
