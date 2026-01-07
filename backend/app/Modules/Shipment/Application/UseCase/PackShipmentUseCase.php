<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventRepository;
use App\Modules\Shipment\Domain\Event\ShipmentEvent;
use App\Modules\Shipment\Domain\Enum\ShipmentEventType;

final class PackShipmentUseCase
{
    public function __construct(
        private ShipmentRepository $shipments,
        private ShipmentEventRepository $events,
    ) {
    }

    public function handle(int $shipmentId): void
    {
        $shipment = $this->shipments->findById($shipmentId);

        // 冪等
        if ($shipment->status()->isPacked()) {
            return;
        }

        $packedShipment = $shipment->pack();

        $this->shipments->save($packedShipment);

        if (! $this->events->exists($shipmentId, ShipmentEventType::PACKED)) {
            $this->events->record(
                ShipmentEvent::packed($shipmentId)
            );
        }
    }
}
