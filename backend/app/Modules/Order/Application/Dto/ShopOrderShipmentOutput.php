<?php

namespace App\Modules\Order\Application\Dto;

final class ShopOrderShipmentOutput
{
    private function __construct(
        private int $orderId,
        private ?int $shipmentId,
        private string $status,
        private ?string $eta,
        private bool $canCreate
    ) {
    }

    public static function notCreated(int $orderId): self
    {
        return new self(
            orderId: $orderId,
            shipmentId: null,
            status: 'not_created',
            eta: null,
            canCreate: true
        );
    }

    public static function fromRow(array $row): self
    {
        return new self(
            orderId: $row['order_id'],
            shipmentId: $row['shipment_id'],
            status: $row['shipment_status'],
            eta: $row['eta'],
            canCreate: false
        );
    }

    public function toArray(): array
    {
        return [
            'order_id'     => $this->orderId,
            'shipment_id'  => $this->shipmentId,
            'status'       => $this->status,
            'eta'          => $this->eta,
            'can_create'   => $this->canCreate,
        ];
    }
}
