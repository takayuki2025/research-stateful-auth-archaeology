<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\AdminLedgerReconciliationQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAdminLedgerReconciliationQueryRepository implements AdminLedgerReconciliationQueryRepository
{
    public function listMissingSales(
        ?array $shopIds,
        string $from,
        string $to,
        string $currency,
        int $limit,
        ?string $cursor
    ): array {
        $limit = max(1, min($limit, 200));

        $qb = DB::table('payments as p')
            ->select([
                'p.id as payment_id',
                'p.order_id',
                'p.shop_id',
                'p.provider_payment_id',
                'p.amount',
                'p.currency',
                'p.method',
                'p.updated_at',
            ])
            ->where('p.status', 'succeeded')
            ->where('p.currency', $currency)
            ->whereNotNull('p.provider_payment_id')
            ->whereBetween('p.updated_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('ledger_postings as lp')
                  ->whereRaw("lp.source_event_id = CONCAT(p.provider_payment_id, ':sale')");
            });

        if (is_array($shopIds) && count($shopIds) > 0) {
            $qb->whereIn('p.shop_id', $shopIds);
        }

        if (is_string($cursor) && ctype_digit($cursor)) {
            $qb->where('p.id', '<', (int)$cursor);
        }

        $rows = $qb->orderByDesc('p.id')->limit($limit + 1)->get();

        $items = [];
        foreach ($rows->take($limit) as $r) {
            $items[] = [
                'payment_id' => (int)$r->payment_id,
                'order_id' => (int)$r->order_id,
                'shop_id' => (int)$r->shop_id,
                'provider_payment_id' => (string)$r->provider_payment_id,
                'amount' => (int)$r->amount,
                'currency' => (string)$r->currency,
                'method' => (string)$r->method,
                'updated_at' => (string)$r->updated_at,
            ];
        }

        $next = null;
        if ($rows->count() > $limit) {
            $next = (string)$rows->last()->payment_id;
        }

        return ['items' => $items, 'next_cursor' => $next];
    }
}