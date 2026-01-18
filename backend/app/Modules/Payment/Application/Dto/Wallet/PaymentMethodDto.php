<?php

namespace App\Modules\Payment\Application\Dto\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\StoredPaymentMethod;

final class PaymentMethodDto
{
    public function __construct(
        public int $id,
        public string $provider,
        public string $providerPaymentMethodId,
        public ?string $brand,
        public ?string $last4,
        public ?int $expMonth,
        public ?int $expYear,
        public bool $isDefault,
    ) {
    }

    public static function fromEntity(StoredPaymentMethod $pm): self
    {
        return new self(
            id: $pm->id(),
            provider: $pm->provider(),
            providerPaymentMethodId: $pm->providerPaymentMethodId(),
            brand: $pm->brand(),
            last4: $pm->last4(),
            expMonth: $pm->expMonth(),
            expYear: $pm->expYear(),
            isDefault: $pm->isDefault(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_payment_method_id' => $this->providerPaymentMethodId,
            'brand' => $this->brand,
            'last4' => $this->last4,
            'exp_month' => $this->expMonth,
            'exp_year' => $this->expYear,
            'is_default' => $this->isDefault,
        ];
    }
}