<?php

namespace App\Modules\Payment\Application\UseCase\Accounts;

use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use App\Modules\Payment\Domain\Account\Repository\HoldRepository;
use Illuminate\Support\Facades\DB;

final class ReleaseHoldUseCase
{
    public function __construct(
        private BalanceRepository $balances,
        private HoldRepository $holds,
    ) {
    }

    public function handle(int $holdId): void
    {
        DB::transaction(function () use ($holdId) {

            $hold = $this->holds->findById($holdId);
            if (! $hold) {
                throw new \DomainException('Hold not found');
            }
            if ($hold['status'] !== 'active') {
                return; // 冪等（released/cancelledなら何もしない）
            }

            $accountId = (int)$hold['account_id'];
            $amount = (int)$hold['amount'];

            $b = $this->balances->lockForUpdate($accountId);

            // ✅ balance 更新：held -= amount, available += amount
            $newHeld = $b['held_amount'] - $amount;
            if ($newHeld < 0) {
                throw new \DomainException('held_amount underflow');
            }

            $newAvailable = $b['available_amount'] + $amount;

            $this->balances->updateAmounts(
                accountId: $accountId,
                available: $newAvailable,
                pending: $b['pending_amount'],
                held: $newHeld,
                calculatedAt: new \DateTimeImmutable(),
            );

            // ✅ hold released
            $this->holds->markReleased($holdId, new \DateTimeImmutable());
        });
    }
}
