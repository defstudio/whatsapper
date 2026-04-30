<?php

namespace DefStudio\Whatsapper\Contracts;

use DefStudio\Whatsapper\Dto\WhatsappContact;
use DefStudio\Whatsapper\Dto\WhatsappMessageContext;

interface WhatsappMessage
{
    public function toRequestBody(): array;

    public static function build(array $data): static;

    public function context(): ?WhatsappMessageContext;

    public function from(): WhatsappContact;
}
