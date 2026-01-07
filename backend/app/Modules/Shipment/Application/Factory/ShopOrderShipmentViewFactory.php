<?php

namespace App\Modules\Shipment\Application\Factory;

use App\Modules\Shipment\Application\Dto\ShopOrderShipmentView;

final class ShopOrderShipmentViewFactory
{
    public function fromRow(array $row): ShopOrderShipmentView
    {
        if (! $row['order_paid']) {
            return ShopOrderShipmentView::notCreated(
                orderId: $row['order_id'],
                canCreate: false,
                destinationAddress: $row['destination_address'],
            );
        }

        if ($row['shipment_id'] === null) {
            return ShopOrderShipmentView::notCreated(
                orderId: $row['order_id'],
                canCreate: true,
                destinationAddress: $row['destination_address'],
            );
        }

        return match ($row['shipment_status']) {
            'draft' => ShopOrderShipmentView::draft(
                orderId: $row['order_id'],
                shipmentId: $row['shipment_id'],
                destinationAddress: $row['destination_address'],
            ),

            'packed' => ShopOrderShipmentView::packed(
                orderId: $row['order_id'],
                shipmentId: $row['shipment_id'],
                destinationAddress: $row['destination_address'],
            ),

            'shipped' => ShopOrderShipmentView::shipped(
                orderId: $row['order_id'],
                shipmentId: $row['shipment_id'],
                eta: $row['eta'],
                destinationAddress: $row['destination_address'],
            ),

            'in_transit' => ShopOrderShipmentView::inTransit(
                orderId: $row['order_id'],
                shipmentId: $row['shipment_id'],
                eta: $row['eta'],
                destinationAddress: $row['destination_address'],
            ),

            'delivered' => ShopOrderShipmentView::delivered(
                orderId: $row['order_id'],
                shipmentId: $row['shipment_id'],
                eta: $row['eta'],
                deliveredAt: $row['delivered_at'] ?? null,
                destinationAddress: $row['destination_address'],
            ),
        };
    }
}
