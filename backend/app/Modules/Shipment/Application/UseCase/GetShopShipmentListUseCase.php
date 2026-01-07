<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentQueryRepository;
use App\Modules\Shipment\Application\Factory\ShopOrderShipmentViewFactory;

final class GetShopShipmentListUseCase
{
    public function __construct(
        private ShipmentQueryRepository $shipments,
        private ShopOrderShipmentViewFactory $factory,
    ) {
    }

    /**
     * @return array<\App\Modules\Shipment\Application\Dto\ShopOrderShipmentView>
     */
    public function handle(int $shopId): array
    {
        // ① raw rows
        $rows = $this->shipments->findOrderShipmentListByShopId($shopId);

        // ② DTO へ変換
        return array_map(
            fn (array $row) => $this->factory->fromRow($row),
            $rows
        );
    }
}
