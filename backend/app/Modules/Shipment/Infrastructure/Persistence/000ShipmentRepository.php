<?php

namespace App\Modules\Shipment\Infrastructure\Persistence;

use App\Modules\Shipment\Domain\Entity\Shipment;
use Carbon\Carbon;

interface ShipmentRepository
{
    public function create(
        int $shopId,
        int $orderId,
        array $origin,
        array $destination,
        Carbon $eta
    ): Shipment;

    public function find(int $id): Shipment;

    public function save(Shipment $shipment): void;
}
