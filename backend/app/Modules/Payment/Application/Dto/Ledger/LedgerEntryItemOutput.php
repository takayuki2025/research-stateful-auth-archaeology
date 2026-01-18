<?php

namespace App\Modules\Payment\Application\Dto\Ledger;

final class LedgerEntryItemOutput
{
    /**
     * @param array<int, array{account_code:string, side:string, amount:int}> $entries
     */
    public function __construct(
        public int $posting_id,
        public string $occurred_at,
        public string $posting_type,
        public ?int $order_id,
        public ?int $payment_id,
        public string $source_provider,
        public string $source_event_id,
        public string $currency,
        public array $entries,
    ) {
    }

    public function toArray(): array
    {
        return [
            'posting_id' => $this->posting_id,
            'occurred_at' => $this->occurred_at,
            'posting_type' => $this->posting_type,
            'order_id' => $this->order_id,
            'payment_id' => $this->payment_id,
            'source_provider' => $this->source_provider,
            'source_event_id' => $this->source_event_id,
            'currency' => $this->currency,
            'entries' => $this->entries,
        ];
    }
}