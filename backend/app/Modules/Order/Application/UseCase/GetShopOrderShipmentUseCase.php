<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentQueryRepository;
use App\Modules\Shipment\Application\Factory\ShopOrderShipmentViewFactory;
use App\Modules\Shipment\Application\Dto\ShopOrderShipmentView;

final class GetShopOrderShipmentUseCase
{
    public function __construct(
        private ShipmentQueryRepository $shipments,
        private ShopOrderShipmentViewFactory $factory,
    ) {
    }

    public function handle(
        int $shopId,
        int $orderId
    ): ShopOrderShipmentView {

        $row = $this->shipments->findByShopIdAndOrderId(
            shopId: $shopId,
            orderId: $orderId
        );

\Log::info('[🔥After pack] Query result', [
    'row' => $row,
]);

        if (! $row) {
            // 未作成（not_created）
            return ShopOrderShipmentView::notCreated(
                orderId: $orderId,
                canCreate: false
            );
        }

        return $this->factory->fromRow($row);
    }
}
