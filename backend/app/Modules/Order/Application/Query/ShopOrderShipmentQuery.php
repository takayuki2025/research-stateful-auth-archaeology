<?php

namespace App\Modules\Order\Application\Query;


use App\Modules\Shipment\Domain\Repository\ShipmentQueryRepository;

final class ShopOrderShipmentQuery
{
    public function __construct(
        private ShipmentQueryRepository $shipments,
    ) {
    }

    public function handle(int $shopId, int $orderId): ?array
    {
        return $this->shipments
            ->findByShopIdAndOrderId($shopId, $orderId);
    }
}
