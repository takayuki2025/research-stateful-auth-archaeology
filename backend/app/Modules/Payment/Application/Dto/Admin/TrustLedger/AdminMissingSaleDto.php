<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminMissingSaleDto
{
    public function __construct(
        public readonly int $payment_id,
        public readonly int $order_id,
        public readonly int $shop_id,
        public readonly string $provider_payment_id,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $method,
        public readonly string $updated_at,
    ) {
    }

    public function toArray(): array
    {
        return [
            'payment_id' => $this->payment_id,
            'order_id' => $this->order_id,
            'shop_id' => $this->shop_id,
            'provider_payment_id' => $this->provider_payment_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'updated_at' => $this->updated_at,
        ];
    }
}