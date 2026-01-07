<?php

namespace App\Modules\Shipment\Infrastructure\Persistence\Query;

use App\Modules\Shipment\Domain\Repository\ShipmentEventReadRepository;
use Illuminate\Support\Facades\DB;

final class DbShipmentEventReadRepository implements ShipmentEventReadRepository
{
    public function findDeliveredAtByShipmentId(int $shipmentId): ?string
    {
        return DB::table('shipment_events')
            ->where('shipment_id', $shipmentId)
            ->where('type', 'delivered')
            ->max('occurred_at');
    }
}
