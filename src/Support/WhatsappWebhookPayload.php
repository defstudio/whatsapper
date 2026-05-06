<?php

namespace DefStudio\Whatsapper\Support;

readonly class WhatsappWebhookPayload
{
    public function __construct(
        public array $payload,
    ) {}

    public function messages(): array
    {
        $messages = [];

        foreach ($this->changes() as $change) {
            $value = $change['value'] ?? [];

            foreach (($value['messages'] ?? []) as $message) {
                $messages[] = [
                    'message' => $message,
                    'metadata' => $value['metadata'] ?? null,
                    'contacts' => $value['contacts'] ?? null,
                    'change' => $change,
                ];
            }
        }

        return $messages;
    }

    public function statuses(): array
    {
        $statuses = [];

        foreach ($this->changes() as $change) {
            $value = $change['value'] ?? [];

            foreach (($value['statuses'] ?? []) as $status) {
                $statuses[] = [
                    'status' => $status,
                    'metadata' => $value['metadata'] ?? null,
                    'change' => $change,
                ];
            }
        }

        return $statuses;
    }

    public function otherChanges(): array
    {
        return array_values(array_filter(
            $this->changes(),
            fn (array $change): bool => ($change['field'] ?? null) !== 'messages'
        ));
    }

    protected function changes(): array
    {
        $changes = [];

        foreach (($this->payload['entry'] ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $changes[] = $change;
            }
        }

        return $changes;
    }
}
