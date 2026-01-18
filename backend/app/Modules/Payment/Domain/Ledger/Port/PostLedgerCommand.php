<?php

namespace App\Modules\Payment\Domain\Ledger\Port;

final class PostLedgerCommand
{
    public function __construct(
        public string $source_provider,   // stripe
        public string $source_event_id,   // pi_xxx:sale / txn_xxx:fee / refundId:refund
        public int $shop_id,
        public ?int $order_id,
        public ?int $payment_id,
        public string $posting_type,      // sale/refund/fee
        public int $amount,               // positive
        public string $currency,          // JPY
        public string $occurred_at,       // 'Y-m-d H:i:s' (JST運用のまま固定)
        public array $meta = [],          // 任意
        public bool $replay = false,      // replay由来か
    ) {
    }

    public function toArray(): array
    {
        return [
            'source_provider' => $this->source_provider,
            'source_event_id' => $this->source_event_id,
            'shop_id' => $this->shop_id,
            'order_id' => $this->order_id,
            'payment_id' => $this->payment_id,
            'posting_type' => $this->posting_type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'occurred_at' => $this->occurred_at,
            'meta' => $this->meta,
            'replay' => $this->replay,
        ];
    }
}