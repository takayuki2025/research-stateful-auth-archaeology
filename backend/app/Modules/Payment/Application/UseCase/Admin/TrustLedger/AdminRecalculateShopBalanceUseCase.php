<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\UseCase\Accounts\RecalculateBalanceUseCase;

final class AdminRecalculateShopBalanceUseCase
{
    public function __construct(
        private RecalculateBalanceUseCase $recalc,
    ) {
    }

    public function handle(int $shopId, string $from, string $to, string $currency = 'JPY'): int
    {
        return $this->recalc->handle($shopId, $from, $to, $currency);
    }
}