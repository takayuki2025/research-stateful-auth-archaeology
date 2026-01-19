<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminWebhookEventDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $provider,
        public readonly string $event_id,
        public readonly string $event_type,
        public readonly string $status,
        public readonly ?int $payment_id,
        public readonly ?int $order_id,
        public readonly ?string $error_message,
        public readonly string $created_at,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'event_id' => $this->event_id,
            'event_type' => $this->event_type,
            'status' => $this->status,
            'payment_id' => $this->payment_id,
            'order_id' => $this->order_id,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
        ];
    }
}