<?php

namespace DefStudio\Whatsapper\Contracts;

use Carbon\Carbon;
use DefStudio\Whatsapper\Dto\WhatsappContact;
use DefStudio\Whatsapper\Dto\WhatsappMessageContext;

interface WhatsappMessageStatusChange
{
    public static function build(array $data): static;

    public function timestamp(): Carbon;

    public function type(): string;

    public function context(): ?WhatsappMessageContext;

    public function from(): WhatsappContact;
}
