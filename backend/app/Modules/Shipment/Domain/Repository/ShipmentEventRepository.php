<?php

namespace App\Modules\Shipment\Domain\Repository;

use App\Modules\Shipment\Domain\Event\ShipmentEvent;
use App\Modules\Shipment\Domain\Enum\ShipmentEventType;

interface ShipmentEventRepository
{
    public function record(ShipmentEvent $event): void;

    public function exists(
        int $shipmentId,
        ShipmentEventType $type
    ): bool;
}
