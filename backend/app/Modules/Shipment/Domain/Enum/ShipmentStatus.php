<?php

namespace App\Modules\Shipment\Domain\Enum;

enum ShipmentStatus: string
{
    case DRAFT = 'draft';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';

    /* ============================
       State check helpers
    ============================ */

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isPacked(): bool
    {
        return $this === self::PACKED;
    }

    public function isShipped(): bool
    {
        return $this === self::SHIPPED;
    }

    public function isInTransit(): bool
    {
        return $this === self::IN_TRANSIT;
    }

    public function isDelivered(): bool
    {
        return $this === self::DELIVERED;
    }
}
