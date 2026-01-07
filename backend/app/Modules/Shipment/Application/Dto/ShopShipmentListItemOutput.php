<?php

namespace App\Modules\Shipment\Application\Dto;

final class ShopShipmentListItemOutput
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $orderStatus,
        public readonly int $totalAmount,
        public readonly string $currency,
        public readonly int $buyerUserId,
        public readonly ?string $addressConfirmedAt, // ✅
        public readonly ?int $shipmentId,
        public readonly ?string $shipmentStatus,
        public readonly ?string $eta,
        public readonly ?array $destinationAddress,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            orderId: (int) $row['order_id'],
            orderStatus: (string) $row['order_status'],
            totalAmount: (int) $row['total_amount'],
            currency: (string) $row['currency'],
            buyerUserId: (int) $row['buyer_user_id'],
            addressConfirmedAt: $row['address_confirmed_at']
                ? (string) $row['address_confirmed_at']
                : null,
            shipmentId: $row['shipment_id'] !== null ? (int) $row['shipment_id'] : null,
            shipmentStatus: $row['shipment_status'] ?: null,
            eta: $row['eta'] ?: null,
            destinationAddress: is_array($row['destination_address'] ?? null)
                ? $row['destination_address']
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_status' => $this->orderStatus,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'buyer_user_id' => $this->buyerUserId,
            'address_confirmed_at' => $this->addressConfirmedAt,

            'shipment_id' => $this->shipmentId,
            'shipment_status' => $this->shipmentStatus,
            'eta' => $this->eta,
            'destination_address' => $this->destinationAddress,
        ];
    }
}
