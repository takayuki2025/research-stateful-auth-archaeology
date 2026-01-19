<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface AdminLedgerReconciliationQueryRepository
{
    /**
     * payments(succeeded) はあるが ledger_postings(pi:sale) が無いもの
     *
     * @param int[]|null $shopIds
     * @return array{items:array<int,array<string,mixed>>, next_cursor:?string}
     */
    public function listMissingSales(
        ?array $shopIds,
        string $from,
        string $to,
        string $currency,
        int $limit,
        ?string $cursor
    ): array;
}
