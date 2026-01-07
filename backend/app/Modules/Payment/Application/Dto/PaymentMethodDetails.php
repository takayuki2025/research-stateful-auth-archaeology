<?php

namespace App\Modules\Payment\Application\Dto;

final class PaymentMethodDetails
{
    public function __construct(
        public readonly ?string $brand = null,
        public readonly ?string $last4 = null,
        public readonly ?string $paymentMethodId = null, // e.g. pm_xxx
    ) {
    }

    public function toArray(): array
    {
        return [
            'brand' => $this->brand,
            'last4' => $this->last4,
            'payment_method_id' => $this->paymentMethodId,
        ];
    }
}
