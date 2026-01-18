<?php

namespace App\Modules\Payment\Domain\Repository\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\StoredPaymentMethod;

interface StoredPaymentMethodRepository
{
    /** @return StoredPaymentMethod[] */
    public function listActiveByWalletId(int $walletId): array;

    public function upsertActiveCard(
        int $walletId,
        string $provider,
        string $providerPaymentMethodId,
        ?string $brand,
        ?string $last4,
        ?int $expMonth,
        ?int $expYear,
    ): void;
}
