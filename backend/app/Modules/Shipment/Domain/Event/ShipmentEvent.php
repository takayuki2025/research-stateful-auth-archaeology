<?php

namespace App\Modules\Shipment\Domain\Event;

use App\Modules\Shipment\Domain\Enum\ShipmentEventType;

final class ShipmentEvent
{
    public function __construct(
        public readonly int $shipmentId,
        public readonly ShipmentEventType $type,
        public readonly ?array $meta,
        public readonly \DateTimeImmutable $occurredAt,
    ) {
    }

    // --------------------
    // Static factories
    // --------------------

    public static function packed(int $shipmentId, ?array $meta = null): self
    {
        return new self(
            shipmentId: $shipmentId,
            type: ShipmentEventType::PACKED,
            meta: $meta,
            occurredAt: new \DateTimeImmutable(),
        );
    }

    public static function shipped(int $shipmentId, ?array $meta = null): self
    {
        return new self(
            shipmentId: $shipmentId,
            type: ShipmentEventType::SHIPPED,
            meta: $meta,
            occurredAt: new \DateTimeImmutable(),
        );
    }

    public static function inTransit(int $shipmentId, ?array $meta = null): self
    {
        return new self(
            shipmentId: $shipmentId,
            type: ShipmentEventType::IN_TRANSIT,
            meta: $meta,
            occurredAt: new \DateTimeImmutable(),
        );
    }

    public static function delivered(int $shipmentId, ?array $meta = null): self
    {
        return new self(
            shipmentId: $shipmentId,
            type: ShipmentEventType::DELIVERED,
            meta: $meta,
            occurredAt: new \DateTimeImmutable(),
        );
    }
}
