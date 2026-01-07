<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventRepository;
use App\Modules\Shipment\Domain\Event\ShipmentEvent;
use App\Modules\Shipment\Domain\Enum\ShipmentEventType;

final class DeliverShipmentUseCase
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
        if ($shipment->status()->isDelivered()) {
            return;
        }

        // 状態遷移ガード（v1 固定）
        if (! $shipment->status()->isShipped()
            && ! $shipment->status()->isInTransit()
        ) {
            throw new \DomainException(
                'Shipment cannot be delivered from status: ' . $shipment->status()->value
            );
        }

        $deliveredShipment = $shipment->deliver();

        $this->shipments->save($deliveredShipment);

        if (! $this->events->exists($shipmentId, ShipmentEventType::DELIVERED)) {
            $this->events->record(
                ShipmentEvent::delivered($shipmentId)
            );
        }
    }
}
