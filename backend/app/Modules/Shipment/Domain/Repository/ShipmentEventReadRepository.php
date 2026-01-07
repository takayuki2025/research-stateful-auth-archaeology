<?php

namespace App\Modules\Shipment\Domain\Repository;

interface ShipmentEventReadRepository
{
    /**
     * Shipment の delivered イベント発生日時を取得
     *
     * @return string|null 例: "2026-01-05 14:05:15"
     */
    public function findDeliveredAtByShipmentId(int $shipmentId): ?string;
}
