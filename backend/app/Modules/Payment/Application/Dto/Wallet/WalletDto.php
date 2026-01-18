<?php

namespace App\Modules\Payment\Application\Dto\Wallet;

final class WalletDto
{
    /**
     * @param PaymentMethodDto[] $paymentMethods
     */
    public function __construct(
        public bool $exists,
        public array $paymentMethods,
    ) {
    }

    public function toArray(): array
    {
        return [
            'exists' => $this->exists,
            'payment_methods' => array_map(
                fn (PaymentMethodDto $pm) => $pm->toArray(),
                $this->paymentMethods
            ),
        ];
    }
}
