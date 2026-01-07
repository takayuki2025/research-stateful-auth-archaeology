<?php

namespace App\Modules\Shipment\Domain\Event;

enum ShipmentEventType: string
{
    case CREATED    = 'created';
    case PACKED     = 'packed';
    case SHIPPED    = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED  = 'delivered';
}
