<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\LedgerSummaryOutput;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerQueryRepository;

final class GetLedgerSummaryUseCase
{
    public function __construct(
        private LedgerQueryRepository $queries,
    ) {
    }

    public function handle(int $shopId, string $from, string $to): LedgerSummaryOutput
    {
        $s = $this->queries->getSummary($shopId, $from, $to);

        $sales = (int)$s['sales_total'];
        $refund = (int)$s['refund_total'];

        return new LedgerSummaryOutput(
            shop_id: $shopId,
            from: $from,
            to: $to,
            currency: (string)$s['currency'],
            sales_total: $sales,
            refund_total: $refund,
            net_total: $sales - $refund,
            postings_count: (int)$s['postings_count'],
        );
    }
}