<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\AdminPayoutQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAdminPayoutQueryRepository implements AdminPayoutQueryRepository
{
    public function listPayouts(?array $shopIds, string $from, string $to, ?string $status, int $limit, ?string $cursor): array
    {
        $limit = max(1, min($limit, 200));

        $qb = DB::table('payouts as p')
            ->leftJoin('accounts as a', 'a.id', '=', 'p.account_id')
            ->select([
                'p.id as payout_id',
                'p.account_id',
                'a.shop_id as shop_id',
                'p.amount',
                'p.currency',
                'p.rail',
                'p.status',
                'p.created_at',
                'p.updated_at',
            ])
            ->whereBetween('p.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if (is_array($shopIds) && count($shopIds) > 0) {
            $qb->whereIn('a.shop_id', $shopIds);
        }
        if (is_string($status) && $status !== '' && $status !== 'all') {
            $qb->where('p.status', $status);
        }
        if (is_string($cursor) && ctype_digit($cursor)) {
            $qb->where('p.id', '<', (int)$cursor);
        }

        $rows = $qb->orderByDesc('p.id')->limit($limit + 1)->get();

        $items = [];
        foreach ($rows->take($limit) as $r) {
            $items[] = [
                'payout_id' => (int)$r->payout_id,
                'account_id' => (int)$r->account_id,
                'shop_id' => $r->shop_id !== null ? (int)$r->shop_id : null,
                'amount' => (int)$r->amount,
                'currency' => (string)$r->currency,
                'rail' => (string)$r->rail,
                'status' => (string)$r->status,
                'created_at' => (string)$r->created_at,
                'updated_at' => (string)$r->updated_at,
            ];
        }

        $next = null;
        if ($rows->count() > $limit) {
            $next = (string)$rows->last()->payout_id;
        }

        return ['items' => $items, 'next_cursor' => $next];
    }
}