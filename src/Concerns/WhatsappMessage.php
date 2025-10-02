<?php

namespace DefStudio\Whatsapper\Concerns;

interface WhatsappMessage
{
    public function toRequestBody(): array;

    public static function make(mixed ...$arguments): static;
}
