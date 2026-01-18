<?php

namespace App\Modules\Payment\Application\Dto\Ledger;

final class LedgerReconciliationOutput
{
    public function __construct(
        public int $shop_id,
        public string $from,
        public string $to,
        public int $missing_sale_count,
        public array $missing_sales,
    ) {
    }

    public function toArray(): array
    {
        return [
            'shop_id' => $this->shop_id,
            'from' => $this->from,
            'to' => $this->to,
            'missing_sale_count' => $this->missing_sale_count,
            'missing_sales' => $this->missing_sales,
        ];
    }
}