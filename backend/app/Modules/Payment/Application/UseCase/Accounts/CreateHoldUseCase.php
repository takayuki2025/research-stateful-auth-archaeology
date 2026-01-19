<?php

namespace App\Modules\Payment\Application\UseCase\Accounts;

use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use App\Modules\Payment\Domain\Account\Repository\HoldRepository;
use Illuminate\Support\Facades\DB;

final class CreateHoldUseCase
{
    public function __construct(
        private BalanceRepository $balances,
        private HoldRepository $holds,
    ) {
    }

    public function handle(int $accountId, int $amount, string $currency, string $reasonCode, ?array $meta = null): int
    {
        if ($amount <= 0) {
            throw new \DomainException('amount must be > 0');
        }

        return DB::transaction(function () use ($accountId, $amount, $currency, $reasonCode, $meta) {

            $b = $this->balances->lockForUpdate($accountId);

            if ($b['currency'] !== $currency) {
                throw new \DomainException('currency mismatch');
            }

            if ($b['available_amount'] < $amount) {
                throw new \DomainException('insufficient available balance');
            }

            // ✅ hold を作成
            $holdId = $this->holds->create(
                accountId: $accountId,
                amount: $amount,
                currency: $currency,
                reasonCode: $reasonCode,
                meta: $meta,
                heldAt: new \DateTimeImmutable(),
            );

            // ✅ balance 更新：available -= amount, held += amount
            $newAvailable = $b['available_amount'] - $amount;
            $newHeld = $b['held_amount'] + $amount;

            $this->balances->updateAmounts(
                accountId: $accountId,
                available: $newAvailable,
                pending: $b['pending_amount'],
                held: $newHeld,
                calculatedAt: new \DateTimeImmutable(),
            );

            return $holdId;
        });
    }
}