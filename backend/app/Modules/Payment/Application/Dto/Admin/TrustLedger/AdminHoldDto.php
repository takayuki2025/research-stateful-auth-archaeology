<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminHoldDto
{
    public function __construct(
        public readonly int $hold_id,
        public readonly int $account_id,
        public readonly ?int $shop_id,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $reason_code,
        public readonly string $status,
        public readonly string $created_at,
        public readonly ?string $released_at,
    ) {
    }

    public function toArray(): array
    {
        return [
            'hold_id' => $this->hold_id,
            'account_id' => $this->account_id,
            'shop_id' => $this->shop_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reason_code' => $this->reason_code,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'released_at' => $this->released_at,
        ];
    }
}