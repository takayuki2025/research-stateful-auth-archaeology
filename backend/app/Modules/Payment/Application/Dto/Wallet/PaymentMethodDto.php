<?php

namespace App\Modules\Payment\Application\Dto\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\StoredPaymentMethod;

final class PaymentMethodDto
{
    public function __construct(
        public int $id,
        public string $provider,
        public string $providerPaymentMethodId,

        // ✅ 追加
        public string $source,

        public ?string $brand,
        public ?string $last4,
        public ?int $expMonth,
        public ?int $expYear,
        public bool $isDefault,

        // ✅ 追加（P6: UI側でボタン制御）
        public bool $oneClickEligible,
    ) {
    }

    public static function fromEntity(StoredPaymentMethod $pm): self
    {
        $source = $pm->source();

        return new self(
            id: $pm->id(),
            provider: $pm->provider(),
            providerPaymentMethodId: $pm->providerPaymentMethodId(),
            source: $source,
            brand: $pm->brand(),
            last4: $pm->last4(),
            expMonth: $pm->expMonth(),
            expYear: $pm->expYear(),
            isDefault: $pm->isDefault(),
            oneClickEligible: ($source === 'card'), // v1固定
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_payment_method_id' => $this->providerPaymentMethodId,

            // ✅ 追加
            'source' => $this->source,

            'brand' => $this->brand,
            'last4' => $this->last4,
            'exp_month' => $this->expMonth,
            'exp_year' => $this->expYear,
            'is_default' => $this->isDefault,

            // ✅ 追加
            'one_click_eligible' => $this->oneClickEligible,
        ];
    }
}