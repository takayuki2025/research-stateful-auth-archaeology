<?php

namespace App\Modules\Payment\Application\Dto\Ledger;

final class LedgerSummaryOutput
{
    public function __construct(
        public int $shop_id,
        public string $from,
        public string $to,
        public string $currency,
        public int $sales_total,
        public int $refund_total,
        public int $net_total,
        public int $postings_count,
    ) {
    }

    public function toArray(): array
    {
        return [
            'shop_id' => $this->shop_id,
            'from' => $this->from,
            'to' => $this->to,
            'currency' => $this->currency,
            'sales_total' => $this->sales_total,
            'refund_total' => $this->refund_total,
            'net_total' => $this->net_total,
            'postings_count' => $this->postings_count,
        ];
    }
}