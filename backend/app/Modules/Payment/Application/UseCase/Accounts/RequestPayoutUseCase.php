<?php

namespace App\Modules\Payment\Application\UseCase\Accounts;

use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use App\Modules\Payment\Domain\Account\Repository\PayoutRepository;
use Illuminate\Support\Facades\DB;

final class RequestPayoutUseCase
{
    public function __construct(
        private BalanceRepository $balances,
        private PayoutRepository $payouts,
    ) {
    }

    /**
     * 最小：available から引いて pending に積む（holdとは別）
     */
    public function handle(int $accountId, int $amount, string $currency, string $rail = 'stripe', ?array $meta = null): int
    {
        if ($amount <= 0) {
            throw new \DomainException('amount must be > 0');
        }

        return DB::transaction(function () use ($accountId, $amount, $currency, $rail, $meta) {

            $b = $this->balances->lockForUpdate($accountId);

            if ($b['currency'] !== $currency) {
                throw new \DomainException('currency mismatch');
            }

            if ($b['available_amount'] < $amount) {
                throw new \DomainException('insufficient available balance');
            }

            // ✅ payout 作成
            $payoutId = $this->payouts->create(
                accountId: $accountId,
                amount: $amount,
                currency: $currency,
                rail: $rail,
                providerPayoutId: null,
                meta: $meta,
                requestedAt: new \DateTimeImmutable(),
            );

            // ✅ balance 更新：available -= amount, pending += amount
            $newAvailable = $b['available_amount'] - $amount;
            $newPending = $b['pending_amount'] + $amount;

            $this->balances->updateAmounts(
                accountId: $accountId,
                available: $newAvailable,
                pending: $newPending,
                held: $b['held_amount'],
                calculatedAt: new \DateTimeImmutable(),
            );

            return $payoutId;
        });
    }
}