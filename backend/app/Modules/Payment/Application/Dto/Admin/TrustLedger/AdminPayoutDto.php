<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminPayoutDto
{
    public function __construct(
        public readonly int $payout_id,
        public readonly int $account_id,
        public readonly ?int $shop_id,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $rail,
        public readonly string $status,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {
    }

    public function toArray(): array
    {
        return [
            'payout_id' => $this->payout_id,
            'account_id' => $this->account_id,
            'shop_id' => $this->shop_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'rail' => $this->rail,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
