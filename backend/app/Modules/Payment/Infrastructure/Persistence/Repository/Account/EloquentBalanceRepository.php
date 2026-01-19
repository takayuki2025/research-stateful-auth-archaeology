<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use Illuminate\Support\Facades\DB;

final class EloquentBalanceRepository implements BalanceRepository
{
    public function upsert(int $accountId, int $available, int $pending, int $held, string $currency, \DateTimeImmutable $calculatedAt): void
    {
        DB::table('balances')->updateOrInsert(
            ['account_id' => $accountId],
            [
                'available_amount' => $available,
                'pending_amount' => $pending,
                'held_amount' => $held,
                'currency' => $currency,
                'calculated_at' => $calculatedAt->format('Y-m-d H:i:s'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function findByAccountId(int $accountId): ?array
    {
        $row = DB::table('balances')->where('account_id', $accountId)->first();
        if (! $row) return null;

        return [
            'account_id' => (int)$row->account_id,
            'available_amount' => (int)$row->available_amount,
            'pending_amount' => (int)$row->pending_amount,
            'held_amount' => (int)$row->held_amount,
            'currency' => (string)$row->currency,
            'calculated_at' => (string)$row->calculated_at,
        ];
    }

    public function lockForUpdate(int $accountId): array
    {
        $row = DB::table('balances')
            ->where('account_id', $accountId)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            // balance が無いのは異常（先に v3-1 recalc で作る前提）
            throw new \DomainException('Balance not found');
        }

        return [
            'account_id' => (int)$row->account_id,
            'available_amount' => (int)$row->available_amount,
            'pending_amount' => (int)$row->pending_amount,
            'held_amount' => (int)$row->held_amount,
            'currency' => (string)$row->currency,
        ];
    }

    public function updateAmounts(int $accountId, int $available, int $pending, int $held, \DateTimeImmutable $calculatedAt): void
    {
        DB::table('balances')->where('account_id', $accountId)->update([
            'available_amount' => $available,
            'pending_amount' => $pending,
            'held_amount' => $held,
            'calculated_at' => $calculatedAt->format('Y-m-d H:i:s'),
            'updated_at' => now(),
        ]);
    }
}