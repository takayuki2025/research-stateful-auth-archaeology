<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface LedgerReconciliationQueryRepository
{
    /**
     * 決済（payments）と台帳（ledger_postings）を突合して欠損を返す。
     * まずは sale posting の欠損だけ検出（v2-4最小）。
     */
    public function findMissingSalePostings(int $shopId, string $fromDate, string $toDate, int $limit): array;
}