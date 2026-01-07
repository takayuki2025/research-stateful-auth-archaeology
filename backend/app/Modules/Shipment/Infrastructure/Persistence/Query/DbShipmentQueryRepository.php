<?php

namespace App\Modules\Shipment\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class DbShipmentQueryRepository
{
    /**
     * Order 単位で Shipment + delivered_at（Event由来）を取得
     */
    public function findByOrderId(int $orderId): ?array
    {
        $row = DB::table('shipments')
            ->leftJoin('shipment_events', function ($join) {
                $join->on('shipments.id', '=', 'shipment_events.shipment_id')
                    ->where('shipment_events.type', 'delivered');
            })
            ->where('shipments.order_id', $orderId)
            ->select([
                'shipments.id as shipment_id',
                'shipments.status as shipment_status',
                'shipments.eta',
                DB::raw('MAX(shipment_events.occurred_at) as delivered_at'),
            ])
            ->groupBy(
                'shipments.id',
                'shipments.status',
                'shipments.eta',
            )
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'shipment_id'    => $row->shipment_id,
            'shipment_status' => $row->shipment_status,
            'eta'            => $row->eta,
            'delivered_at'   => $row->delivered_at,
        ];
    }
}
