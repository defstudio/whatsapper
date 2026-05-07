<?php

namespace DefStudio\Whatsapper\Events;

use DefStudio\Whatsapper\Contracts\WhatsappMessageStatusChange;
use DefStudio\Whatsapper\Dto\MessageDeliveredStatus;
use DefStudio\Whatsapper\Dto\MessageFailedStatus;
use DefStudio\Whatsapper\Dto\MessageReadStatus;
use DefStudio\Whatsapper\Dto\MessageSentStatus;
use DefStudio\Whatsapper\Dto\OutOfSupportWindowStatus;
use DefStudio\Whatsapper\Dto\UnsupportedStatus;

class WhatsappMessageStatusUpdated
{
    public WhatsappMessageStatusChange $statusChange;

    public function __construct(
        public readonly array $status,
        public readonly ?array $metadata,
        public readonly array $rawChange,
        public readonly array $rawPayload,
    ) {
        $this->statusChange = match (true) {
            //  delivered, read, sent,
            $this->status['status'] === 'sent' => MessageSentStatus::build($this->status),
            $this->status['status'] === 'delivered' => MessageDeliveredStatus::build($this->status),
            $this->status['status'] === 'read' => MessageReadStatus::build($this->status),
            $this->status['status'] === 'failed' => match (true) {
                collect($this->status['errors'])
                    ->filter(fn ($error) => $error['code'] === '131047')
                    ->isNotEmpty() => OutOfSupportWindowStatus::build($this->status),
                default => MessageFailedStatus::build($this->status)
            },
            default => UnsupportedStatus::build($this->status),
        };
    }
}
