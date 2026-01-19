<?php

namespace App\Modules\Payment\Application\UseCase\Accounts;

use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use App\Modules\Payment\Domain\Account\Repository\PayoutRepository;
use Illuminate\Support\Facades\DB;

final class MarkPayoutStatusUseCase
{
    public function __construct(
        private BalanceRepository $balances,
        private PayoutRepository $payouts,
    ) {
    }

    /**
     * status: processing | paid | failed
     * - failed の場合は pending を available に戻す
     * - paid の場合は pending 減算（=確定）
     */
    public function handle(int $payoutId, string $status, ?string $providerPayoutId = null, ?array $meta = null): void
    {
        if (!in_array($status, ['processing', 'paid', 'failed'], true)) {
            throw new \DomainException('invalid payout status');
        }

        DB::transaction(function () use ($payoutId, $status, $providerPayoutId, $meta) {

            $payout = $this->payouts->findById($payoutId);
            if (! $payout) {
                throw new \DomainException('Payout not found');
            }

            // 冪等：同じstatusなら何もしない
            if ($payout['status'] === $status) {
                return;
            }

            // 状態遷移の最小ガード
            $current = $payout['status'];
            $allowed = [
                'requested' => ['processing', 'failed'],
                'processing' => ['paid', 'failed'],
                'paid' => [],
                'failed' => [],
            ];

            if (!in_array($status, $allowed[$current] ?? [], true)) {
                throw new \DomainException("invalid transition: {$current} -> {$status}");
            }

            $accountId = (int)$payout['account_id'];
            $amount = (int)$payout['amount'];
            $currency = (string)$payout['currency'];

            $b = $this->balances->lockForUpdate($accountId);
            if ($b['currency'] !== $currency) {
                throw new \DomainException('currency mismatch');
            }

            // pending の取り扱い
            if ($status === 'paid') {
                // pending を減らして確定（available は戻さない）
                $newPending = $b['pending_amount'] - $amount;
                if ($newPending < 0) throw new \DomainException('pending underflow');

                $this->balances->updateAmounts(
                    accountId: $accountId,
                    available: $b['available_amount'],
                    pending: $newPending,
                    held: $b['held_amount'],
                    calculatedAt: new \DateTimeImmutable(),
                );
            }

            if ($status === 'failed') {
                // pending を戻して available に返す
                $newPending = $b['pending_amount'] - $amount;
                if ($newPending < 0) throw new \DomainException('pending underflow');

                $newAvailable = $b['available_amount'] + $amount;

                $this->balances->updateAmounts(
                    accountId: $accountId,
                    available: $newAvailable,
                    pending: $newPending,
                    held: $b['held_amount'],
                    calculatedAt: new \DateTimeImmutable(),
                );
            }

            // processing は balance はそのまま（requested→processingで金額拘束済み）

            $this->payouts->updateStatus(
                payoutId: $payoutId,
                status: $status,
                providerPayoutId: $providerPayoutId,
                meta: $meta,
                now: new \DateTimeImmutable(),
            );
        });
    }
}