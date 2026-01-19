<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface LedgerBalanceQueryRepository
{
    /**
     * v3-1 最小：CASH_CLEARING の正味を返す
     * return: available_amount
     */
    public function sumCashClearingNet(int $shopId, string $currency, string $fromDate, string $toDate): int;
}