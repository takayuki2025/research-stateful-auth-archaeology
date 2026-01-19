<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminLedgerEntryLineDto
{
    public function __construct(
        public readonly string $account_code,
        public readonly string $side,
        public readonly int $amount,
        public readonly string $currency,
    ) {
    }

    public function toArray(): array
    {
        return [
            'account_code' => $this->account_code,
            'side' => $this->side,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}