<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Admin\TrustLedger\AdminKpiDto;
use App\Modules\Payment\Domain\Ledger\Repository\AdminLedgerKpiQueryRepository;

final class GetGlobalKpiUseCase
{
    public function __construct(
        private AdminLedgerKpiQueryRepository $kpis,
    ) {
    }

    public function handle(string $from, string $to, string $currency): AdminKpiDto
    {
        $r = $this->kpis->getGlobalKpi($from, $to, $currency);

        $sales = (int)$r['sales'];
        $refund = (int)$r['refund'];
        $fee = (int)$r['fee'];
        $count = (int)$r['postings_count'];

        return new AdminKpiDto(
            from: $from,
            to: $to,
            currency: $currency,
            sales_total: $sales,
            refund_total: $refund,
            fee_total: $fee,
            net_total: $sales - $refund - $fee,
            postings_count: $count,
        );
    }
}