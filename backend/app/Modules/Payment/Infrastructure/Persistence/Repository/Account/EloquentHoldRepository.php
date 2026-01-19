<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\HoldRepository;
use Illuminate\Support\Facades\DB;

final class EloquentHoldRepository implements HoldRepository
{
    public function create(int $accountId, int $amount, string $currency, string $reasonCode, ?array $meta, \DateTimeImmutable $heldAt): int
    {
        return (int) DB::table('holds')->insertGetId([
            'account_id' => $accountId,
            'amount' => $amount,
            'currency' => $currency,
            'reason_code' => $reasonCode,
            'status' => 'active',
            'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'held_at' => $heldAt->format('Y-m-d H:i:s'),
            'released_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findById(int $holdId): ?array
    {
        $row = DB::table('holds')->where('id', $holdId)->first();
        if (! $row) return null;

        return [
            'id' => (int)$row->id,
            'account_id' => (int)$row->account_id,
            'amount' => (int)$row->amount,
            'currency' => (string)$row->currency,
            'reason_code' => (string)$row->reason_code,
            'status' => (string)$row->status,
            'held_at' => (string)$row->held_at,
            'released_at' => $row->released_at ? (string)$row->released_at : null,
            'meta' => $row->meta ? json_decode($row->meta, true) : null,
        ];
    }

    public function markReleased(int $holdId, \DateTimeImmutable $releasedAt): void
    {
        DB::table('holds')->where('id', $holdId)->update([
            'status' => 'released',
            'released_at' => $releasedAt->format('Y-m-d H:i:s'),
            'updated_at' => now(),
        ]);
    }
}
