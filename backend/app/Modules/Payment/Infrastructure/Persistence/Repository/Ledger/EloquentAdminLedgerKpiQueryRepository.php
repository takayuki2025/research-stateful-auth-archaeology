<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\AdminLedgerKpiQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAdminLedgerKpiQueryRepository implements AdminLedgerKpiQueryRepository
{
    public function getGlobalKpi(string $from, string $to, string $currency): array
    {
        $rows = DB::table('ledger_postings')
            ->select('posting_type', DB::raw('SUM(amount) as s'), DB::raw('COUNT(*) as c'))
            ->where('currency', $currency)
            ->whereBetween('occurred_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereIn('posting_type', ['sale', 'refund', 'fee'])
            ->groupBy('posting_type')
            ->get();

        $sales = 0; $refund = 0; $fee = 0; $count = 0;
        foreach ($rows as $r) {
            $count += (int)$r->c;
            if ($r->posting_type === 'sale') $sales = (int)$r->s;
            if ($r->posting_type === 'refund') $refund = (int)$r->s;
            if ($r->posting_type === 'fee') $fee = (int)$r->s;
        }

        return ['sales' => $sales, 'refund' => $refund, 'fee' => $fee, 'postings_count' => $count];
    }

    public function getShopKpis(?array $shopIds, string $from, string $to, string $currency): array
    {
        $q = DB::table('ledger_postings')
            ->select('shop_id', 'posting_type', DB::raw('SUM(amount) as s'), DB::raw('COUNT(*) as c'))
            ->where('currency', $currency)
            ->whereBetween('occurred_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereIn('posting_type', ['sale', 'refund', 'fee'])
            ->groupBy('shop_id', 'posting_type');

        if (is_array($shopIds) && count($shopIds) > 0) {
            $q->whereIn('shop_id', $shopIds);
        }

        $rows = $q->get();

        /** @var array<int, array{shop_id:int,sales:int,refund:int,fee:int,postings_count:int}> $map */
        $map = [];
        foreach ($rows as $r) {
            $sid = (int)$r->shop_id;
            if (!isset($map[$sid])) {
                $map[$sid] = ['shop_id' => $sid, 'sales' => 0, 'refund' => 0, 'fee' => 0, 'postings_count' => 0];
            }
            $map[$sid]['postings_count'] += (int)$r->c;
            if ($r->posting_type === 'sale') $map[$sid]['sales'] = (int)$r->s;
            if ($r->posting_type === 'refund') $map[$sid]['refund'] = (int)$r->s;
            if ($r->posting_type === 'fee') $map[$sid]['fee'] = (int)$r->s;
        }

        return $map;
    }
}