<?php

namespace App\Modules\Shipment\Domain\Repository;

interface ShipmentManagementQueryRepository
{
    /**
     * Shop視点の配送管理一覧（Order + Shipment）
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByShopId(int $shopId): array;
}
