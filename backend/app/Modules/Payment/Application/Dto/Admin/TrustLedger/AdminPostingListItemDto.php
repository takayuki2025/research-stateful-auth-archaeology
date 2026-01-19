<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminPostingListItemDto
{
    public function __construct(
        public readonly int $posting_id,
        public readonly int $shop_id,
        public readonly string $occurred_at,
        public readonly string $posting_type,
        public readonly ?int $order_id,
        public readonly ?int $payment_id,
        public readonly string $source_provider,
        public readonly string $source_event_id,
        public readonly int $amount,
        public readonly string $currency,
    ) {
    }

    public function toArray(): array
    {
        return [
            'posting_id' => $this->posting_id,
            'shop_id' => $this->shop_id,
            'occurred_at' => $this->occurred_at,
            'posting_type' => $this->posting_type,
            'order_id' => $this->order_id,
            'payment_id' => $this->payment_id,
            'source_provider' => $this->source_provider,
            'source_event_id' => $this->source_event_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}