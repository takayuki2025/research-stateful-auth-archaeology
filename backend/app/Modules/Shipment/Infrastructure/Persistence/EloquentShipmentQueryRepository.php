<?php

namespace App\Modules\Shipment\Infrastructure\Persistence;

use App\Modules\Shipment\Domain\Repository\ShipmentQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentShipmentQueryRepository implements ShipmentQueryRepository
{
    /**
     * ============================
     * 注文 + 配送（単一）
     * ============================
     */
    public function findByShopIdAndOrderId(
        int $shopId,
        int $orderId
    ): ?array {
        $row = DB::table('orders')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.order_id', '=', 'orders.id')
                     ->where('payments.status', 'succeeded');
            })
            ->leftJoin('shipments', 'shipments.order_id', '=', 'orders.id')

            // ✅ delivered event join
            ->leftJoin('shipment_events as delivered_events', function ($join) {
                $join->on('shipments.id', '=', 'delivered_events.shipment_id')
                     ->where('delivered_events.type', 'delivered');
            })

            ->where('orders.shop_id', $shopId)
            ->where('orders.id', $orderId)

            ->select([
                'orders.id as order_id',
                'orders.status as order_status',
                DB::raw('payments.id IS NOT NULL as order_paid'),

                'shipments.id as shipment_id',
                'shipments.status as shipment_status',
                'shipments.eta',

                // ★ Event 由来の delivered_at
                DB::raw('MAX(delivered_events.occurred_at) as delivered_at'),

                'orders.address_snapshot as destination_address',
            ])
            ->groupBy(
                'orders.id',
                'orders.status',
                'payments.id',
                'shipments.id',
                'shipments.status',
                'shipments.eta',
                'orders.address_snapshot'
            )
            ->first();

\Log::info('[🔥ShipmentQuery row]', $row ? (array) $row : ['row' => null]);

        return $row ? $this->normalizeRow($row) : null;
    }

    /**
     * ============================
     * 注文 + 配送（一覧）
     * ============================
     */
    public function findOrderShipmentListByShopId(int $shopId): array
    {
        $rows = DB::table('orders')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.order_id', '=', 'orders.id')
                     ->where('payments.status', 'succeeded');
            })
            ->leftJoin('shipments', 'shipments.order_id', '=', 'orders.id')

            // ✅ delivered event join
            ->leftJoin('shipment_events as delivered_events', function ($join) {
                $join->on('shipments.id', '=', 'delivered_events.shipment_id')
                     ->where('delivered_events.type', 'delivered');
            })

            ->where('orders.shop_id', $shopId)
            ->select([
                'orders.id as order_id',
                'orders.status as order_status',
                DB::raw('payments.id IS NOT NULL as order_paid'),

                'shipments.id as shipment_id',
                'shipments.status as shipment_status',
                'shipments.eta',

                // ★ Event 由来
                DB::raw('MAX(delivered_events.occurred_at) as delivered_at'),

                'orders.address_snapshot as destination_address',
            ])
            ->groupBy(
                'orders.id',
                'orders.status',
                'payments.id',
                'shipments.id',
                'shipments.status',
                'shipments.eta',
                'orders.address_snapshot'
            )
            ->orderByDesc('orders.id')
            ->get();

        return $rows
            ->map(fn ($row) => $this->normalizeRow($row))
            ->all();
    }

    /**
     * ============================
     * 共通正規化
     * ============================
     */
    private function normalizeRow(object $row): array
    {
        return [
            'order_id' => (int) $row->order_id,
            'order_status' => (string) $row->order_status,
            'order_paid' => (bool) $row->order_paid,

            'shipment_id' => $row->shipment_id !== null
                ? (int) $row->shipment_id
                : null,

            'shipment_status' => $row->shipment_status,
            'eta' => $row->eta,

            // ★ ここが UI に届く
            'delivered_at' => $row->delivered_at,

            'destination_address' => $row->destination_address
                ? json_decode($row->destination_address, true)
                : null,
        ];
    }
}
