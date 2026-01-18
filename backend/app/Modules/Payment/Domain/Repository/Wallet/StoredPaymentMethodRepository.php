<?php

namespace App\Modules\Payment\Domain\Repository\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\StoredPaymentMethod;

interface StoredPaymentMethodRepository
{
    /** @return StoredPaymentMethod[] */
    public function listActiveByWalletId(int $walletId): array;
}