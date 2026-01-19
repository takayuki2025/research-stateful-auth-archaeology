<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface AdminLedgerKpiQueryRepository
{
    /** @return array{sales:int,refund:int,fee:int,postings_count:int} */
    public function getGlobalKpi(string $from, string $to, string $currency): array;

    /**
     * @param int[]|null $shopIds
     * @return array<int, array{shop_id:int,sales:int,refund:int,fee:int,postings_count:int}>
     */
    public function getShopKpis(?array $shopIds, string $from, string $to, string $currency): array;
}