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

    /**
     * v1-3
     */
    public function findRowById(int $id): ?array; // wallet_id, provider, pm_id, is_default, status, source

    public function setDefault(int $walletId, int $methodId): void;

    public function markDetached(int $walletId, int $methodId): void;

    public function findNextActiveId(int $walletId, int $excludeId): ?int;

    public function findDefaultActiveRow(int $walletId): ?array;
}