<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\LedgerReconciliationQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentLedgerReconciliationQueryRepository implements LedgerReconciliationQueryRepository
{
    public function findMissingSalePostings(int $shopId, string $fromDate, string $toDate, int $limit): array
{
    $from = $fromDate . ' 00:00:00';
    $to   = $toDate . ' 23:59:59';
    $limit = max(1, min($limit, 500));

    $rows = DB::table('payments as p')
        ->leftJoin('ledger_postings as lp', function ($join) {
            $join->on('lp.payment_id', '=', 'p.id')
                ->where('lp.posting_type', '=', 'sale');
        })
        ->where('p.shop_id', $shopId)
        ->where('p.status', 'succeeded')
        ->whereBetween('p.updated_at', [$from, $to])
        ->whereNull('lp.id')
        // ✅ ここが重要：p.* を明示して alias する
        ->select([
            DB::raw('p.id as payment_id'),
            DB::raw('p.order_id as order_id'),
            DB::raw('p.shop_id as shop_id'),
            DB::raw('p.provider_payment_id as provider_payment_id'),
            DB::raw('p.amount as amount'),
            DB::raw('p.currency as currency'),
            DB::raw('p.method as method'),
            DB::raw('p.updated_at as updated_at'),
        ])
        ->orderByDesc('p.id')
        ->limit($limit)
        ->get();

    return $rows->map(fn($r) => [
        'payment_id' => (int)$r->payment_id,
        'order_id' => (int)$r->order_id,
        'shop_id' => (int)$r->shop_id,
        'provider_payment_id' => (string)$r->provider_payment_id,
        'amount' => (int)$r->amount,
        'currency' => (string)$r->currency,
        'method' => (string)$r->method,
        'updated_at' => (string)$r->updated_at,
    ])->all();
}
}