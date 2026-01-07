<?php

namespace App\Modules\Shipment\Domain\Repository;

interface ShipmentQueryRepository
{
    /**
     * 注文 + 配送の一覧（Shipment がなくても返る）
     */
    public function findOrderShipmentListByShopId(int $shopId): array;

    /**
     * 注文詳細（1件）
     */
    public function findByShopIdAndOrderId(
        int $shopId,
        int $orderId
    ): ?array;
}
