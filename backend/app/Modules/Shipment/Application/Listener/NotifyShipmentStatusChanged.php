<?php

namespace App\Modules\Shipment\Application\Listener;

use App\Modules\Shipment\Domain\Event\ShipmentEvent;
use App\Modules\Shipment\Domain\Event\ShipmentEventType;

final class NotifyShipmentStatusChanged
{
    public function handle(ShipmentEvent $event): void
    {
        match ($event->type) {
            ShipmentEventType::PACKED =>
                \Log::info('梱包完了通知', ['shipment_id' => $event->shipmentId]),

            ShipmentEventType::SHIPPED =>
                \Log::info('発送通知', ['shipment_id' => $event->shipmentId]),

            ShipmentEventType::DELIVERED =>
                \Log::info('配達完了通知', ['shipment_id' => $event->shipmentId]),

            default => null,
        };
    }
}
