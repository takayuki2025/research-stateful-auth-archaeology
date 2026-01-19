<?php

namespace App\Modules\Payment\Application\UseCase\Accounts;

use App\Modules\Payment\Domain\Account\Repository\AccountRepository;
use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use App\Modules\Payment\Domain\Account\Repository\LedgerBalanceQueryRepository;

final class RecalculateBalanceUseCase
{
    public function __construct(
        private AccountRepository $accounts,
        private BalanceRepository $balances,
        private LedgerBalanceQueryRepository $ledger,
    ) {
    }

    /**
     * v3-1 最小：shop の CASH_CLEARING net を available に入れる
     */
    public function handle(int $shopId, string $from, string $to, string $currency = 'JPY'): int
    {
        $accountId = $this->accounts->findOrCreateForShop($shopId, $currency);

        $available = $this->ledger->sumCashClearingNet($shopId, $currency, $from, $to);
        $pending = 0;

        $this->balances->upsert(
            accountId: $accountId,
            available: $available,
            pending: $pending,
            currency: $currency,
            calculatedAt: new \DateTimeImmutable(),
        );

        return $accountId;
    }
}