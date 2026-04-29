<?php

namespace DefStudio\Whatsapper\Contracts;

interface WhatsappMessage
{
    public function toRequestBody(): array;

    public static function make(mixed ...$arguments): static;
}
