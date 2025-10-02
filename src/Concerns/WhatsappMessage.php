<?php

namespace DefStudio\Whatsapper\Concerns;

interface WhatsappMessage
{
    public function toRequestBody(): array;
}
