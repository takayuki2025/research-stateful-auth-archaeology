<?php

namespace App\Modules\Shipment\Domain\Repository;

use App\Modules\Shipment\Domain\Entity\Shipment;

interface ShipmentRepository
{
    public function save(Shipment $shipment): Shipment;

    public function findById(int $shipmentId): Shipment;

    public function findByOrderId(int $orderId): ?Shipment;

    public function existsByOrderId(int $orderId): bool;
}
