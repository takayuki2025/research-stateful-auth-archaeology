<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\LedgerReconciliationOutput;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerReconciliationQueryRepository;

final class GetLedgerReconciliationUseCase
{
    public function __construct(
        private LedgerReconciliationQueryRepository $queries,
    ) {
    }

    public function handle(int $shopId, string $from, string $to, int $limit): LedgerReconciliationOutput
    {
        $missing = $this->queries->findMissingSalePostings($shopId, $from, $to, $limit);

        return new LedgerReconciliationOutput(
            shop_id: $shopId,
            from: $from,
            to: $to,
            missing_sale_count: count($missing),
            missing_sales: $missing,
        );
    }
}