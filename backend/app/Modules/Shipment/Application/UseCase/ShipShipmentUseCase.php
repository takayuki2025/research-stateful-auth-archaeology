<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventRepository;
use App\Modules\Shipment\Domain\Event\ShipmentEvent;
use App\Modules\Shipment\Domain\Enum\ShipmentEventType;
use Illuminate\Support\Facades\DB;

final class ShipShipmentUseCase
{
    public function __construct(
        private ShipmentRepository $shipments,
        private ShipmentEventRepository $events,
    ) {
    }

    public function handle(int $shipmentId): void
    {
        DB::transaction(function () use ($shipmentId) {

            $shipment = $this->shipments->findById($shipmentId);

            // Aggregate 基準の冪等
            if ($shipment->status()->isShipped()) {
                return;
            }

            $shippedShipment = $shipment->ship(
                new \DateTimeImmutable('+2 days')
            );

            $this->shipments->save($shippedShipment);

            if (! $this->events->exists($shipmentId, ShipmentEventType::SHIPPED)) {
                $this->events->record(
                    ShipmentEvent::shipped($shipmentId)
                );
            }
        });
    }
}
