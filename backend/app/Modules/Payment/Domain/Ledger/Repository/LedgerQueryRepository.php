<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface LedgerQueryRepository
{
    /**
     * summary（postingベースで集計）
     * - sale/refund を posting_type で集計
     */
    public function getSummary(int $shopId, string $fromDate, string $toDate): array;

    /**
     * entries（posting + entries をまとめて返す）
     * 戻り値はDB row配列（UseCaseでDTO化）
     */
    public function listPostingsWithEntries(
        int $shopId,
        string $fromDate,
        string $toDate,
        int $limit,
        ?int $cursorPostingId = null
    ): array;
}