<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Admin\TrustLedger\AdminShopKpiRowDto;
use App\Modules\Payment\Application\Dto\Admin\TrustLedger\CursorPageDto;
use App\Modules\Payment\Domain\Ledger\Repository\AdminLedgerKpiQueryRepository;

final class GetShopKpisUseCase
{
    public function __construct(
        private AdminLedgerKpiQueryRepository $kpis,
    ) {
    }

    /**
     * shop_ids を指定しない場合は「返せるだけ返す」（最小）
     * ※ shop一覧ページングは後で ShopQuery と統合して強化
     */
    public function handle(?array $shopIds, string $from, string $to, string $currency): CursorPageDto
    {
        $map = $this->kpis->getShopKpis($shopIds, $from, $to, $currency);

        $items = [];
        foreach ($map as $row) {
            $dto = new AdminShopKpiRowDto(
                shop_id: (int)$row['shop_id'],
                from: $from,
                to: $to,
                currency: $currency,
                sales_total: (int)$row['sales'],
                refund_total: (int)$row['refund'],
                fee_total: (int)$row['fee'],
                net_total: (int)$row['sales'] - (int)$row['refund'] - (int)$row['fee'],
                postings_count: (int)$row['postings_count'],
            );
            $items[] = $dto->toArray();
        }

        // 最小：ページングは shopIds 指定時のみ想定（next_cursor=null）
        return new CursorPageDto($items, null);
    }
}