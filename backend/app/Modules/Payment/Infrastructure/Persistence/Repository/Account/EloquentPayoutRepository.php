<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\PayoutRepository;
use Illuminate\Support\Facades\DB;

final class EloquentPayoutRepository implements PayoutRepository
{
    public function create(
        int $accountId,
        int $amount,
        string $currency,
        string $rail,
        ?string $providerPayoutId,
        ?array $meta,
        \DateTimeImmutable $requestedAt,
    ): int {
        return (int) DB::table('payouts')->insertGetId([
            'account_id' => $accountId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'requested',
            'rail' => $rail,
            'provider_payout_id' => $providerPayoutId,
            'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'requested_at' => $requestedAt->format('Y-m-d H:i:s'),
            'processed_at' => null,
            'paid_at' => null,
            'failed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findById(int $payoutId): ?array
    {
        $row = DB::table('payouts')->where('id', $payoutId)->first();
        if (! $row) return null;

        return [
            'id' => (int)$row->id,
            'account_id' => (int)$row->account_id,
            'amount' => (int)$row->amount,
            'currency' => (string)$row->currency,
            'status' => (string)$row->status,
            'rail' => (string)$row->rail,
            'provider_payout_id' => $row->provider_payout_id ? (string)$row->provider_payout_id : null,
            'requested_at' => (string)$row->requested_at,
            'processed_at' => $row->processed_at ? (string)$row->processed_at : null,
            'paid_at' => $row->paid_at ? (string)$row->paid_at : null,
            'failed_at' => $row->failed_at ? (string)$row->failed_at : null,
            'meta' => $row->meta ? json_decode($row->meta, true) : null,
        ];
    }

    public function updateStatus(
        int $payoutId,
        string $status,
        ?string $providerPayoutId,
        ?array $meta,
        \DateTimeImmutable $now,
    ): void {
        $updates = [
            'status' => $status,
            'provider_payout_id' => $providerPayoutId,
            'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'updated_at' => now(),
        ];

        if ($status === 'processing') {
            $updates['processed_at'] = $now->format('Y-m-d H:i:s');
        } elseif ($status === 'paid') {
            $updates['paid_at'] = $now->format('Y-m-d H:i:s');
        } elseif ($status === 'failed') {
            $updates['failed_at'] = $now->format('Y-m-d H:i:s');
        }

        DB::table('payouts')->where('id', $payoutId)->update($updates);
    }
}