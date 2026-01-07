<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderQueryRepository;
use App\Modules\Order\Application\Dto\ShopOrderListItemView;

final class GetShopOrdersUseCase
{
    public function __construct(
        private OrderQueryRepository $orders,
    ) {
    }

    /** @return ShopOrderListItemView[] */
    public function handle(int $shopId): array
    {
        $rows = $this->orders->findOrderListWithShipmentByShopId($shopId);

        return array_map(
            fn (array $row) => ShopOrderListItemView::fromRow($row),
            $rows
        );
    }
}
