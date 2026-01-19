<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\AdminHoldQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAdminHoldQueryRepository implements AdminHoldQueryRepository
{
    public function listHolds(?array $shopIds, string $from, string $to, ?string $status, int $limit, ?string $cursor): array
    {
        $limit = max(1, min($limit, 200));

        // accounts テーブルに shop_id がある前提（あなたの recalc が shop→account なので通常そうなります）
        $qb = DB::table('holds as h')
            ->leftJoin('accounts as a', 'a.id', '=', 'h.account_id')
            ->select([
                'h.id as hold_id',
                'h.account_id',
                'a.shop_id as shop_id',
                'h.amount',
                'h.currency',
                'h.reason_code',
                'h.status',
                'h.created_at',
                'h.released_at',
            ])
            ->whereBetween('h.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if (is_array($shopIds) && count($shopIds) > 0) {
            $qb->whereIn('a.shop_id', $shopIds);
        }
        if (is_string($status) && $status !== '' && $status !== 'all') {
            $qb->where('h.status', $status);
        }
        if (is_string($cursor) && ctype_digit($cursor)) {
            $qb->where('h.id', '<', (int)$cursor);
        }

        $rows = $qb->orderByDesc('h.id')->limit($limit + 1)->get();

        $items = [];
        foreach ($rows->take($limit) as $r) {
            $items[] = [
                'hold_id' => (int)$r->hold_id,
                'account_id' => (int)$r->account_id,
                'shop_id' => $r->shop_id !== null ? (int)$r->shop_id : null,
                'amount' => (int)$r->amount,
                'currency' => (string)$r->currency,
                'reason_code' => (string)$r->reason_code,
                'status' => (string)$r->status,
                'created_at' => (string)$r->created_at,
                'released_at' => $r->released_at !== null ? (string)$r->released_at : null,
            ];
        }

        $next = null;
        if ($rows->count() > $limit) {
            $next = (string)$rows->last()->hold_id;
        }

        return ['items' => $items, 'next_cursor' => $next];
    }
}