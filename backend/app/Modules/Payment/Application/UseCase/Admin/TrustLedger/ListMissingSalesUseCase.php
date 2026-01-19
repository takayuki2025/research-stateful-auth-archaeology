<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Admin\TrustLedger\CursorPageDto;
use App\Modules\Payment\Domain\Ledger\Repository\AdminLedgerReconciliationQueryRepository;

final class ListMissingSalesUseCase
{
    public function __construct(
        private AdminLedgerReconciliationQueryRepository $recon,
    ) {
    }

    public function handle(?array $shopIds, string $from, string $to, string $currency, int $limit, ?string $cursor): CursorPageDto
    {
        $r = $this->recon->listMissingSales($shopIds, $from, $to, $currency, $limit, $cursor);
        return new CursorPageDto($r['items'], $r['next_cursor']);
    }
}