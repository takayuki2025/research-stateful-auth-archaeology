<?php

namespace App\Modules\Shipment\Application\Dto;

final class ShopOrderShipmentView
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $status,
        public readonly ?int $shipmentId,
        public readonly ?string $eta,
        public readonly ?string $deliveredAt,
        public readonly bool $canCreate,
        public readonly ?string $nextActionKey,
        public readonly ?string $nextActionLabel,
        public readonly ?array $destinationAddress,
    ) {
    }

    /* ============================
       Named constructors
    ============================ */

    /**
     * 未作成（未入金 or 決済済だが Shipment 未作成）
     */
    public static function notCreated(
        int $orderId,
        bool $canCreate,
        ?array $destinationAddress = null
    ): self {
        return new self(
            orderId: $orderId,
            status: 'not_created',
            shipmentId: null,
            eta: null,
            deliveredAt: null,
            canCreate: $canCreate,
            nextActionKey: $canCreate ? 'accept' : null,
            nextActionLabel: $canCreate ? '配送準備を開始' : null,
            destinationAddress: $destinationAddress,
        );
    }

    /**
     * Shipment 作成済（draft）
     */
    public static function draft(
        int $orderId,
        int $shipmentId,
        ?array $destinationAddress
    ): self {
        return new self(
            orderId: $orderId,
            status: 'draft',
            shipmentId: $shipmentId,
            eta: null,
            deliveredAt: null,
            canCreate: false,
            nextActionKey: 'pack',
            nextActionLabel: '梱包完了',
            destinationAddress: $destinationAddress,
        );
    }

    public static function packed(
        int $orderId,
        int $shipmentId,
        ?array $destinationAddress
    ): self {
        return new self(
            orderId: $orderId,
            status: 'packed',
            shipmentId: $shipmentId,
            eta: null,
            deliveredAt: null,
            canCreate: false,
            nextActionKey: 'ship',
            nextActionLabel: '発送',
            destinationAddress: $destinationAddress,
        );
    }

    public static function shipped(
        int $orderId,
        int $shipmentId,
        ?string $eta,
        ?array $destinationAddress
    ): self {
        return new self(
            orderId: $orderId,
            status: 'shipped',
            shipmentId: $shipmentId,
            eta: $eta,
            deliveredAt: null,
            canCreate: false,
            nextActionKey: 'in-transit',
            nextActionLabel: '輸送中',
            destinationAddress: $destinationAddress,
        );
    }

    public static function inTransit(
        int $orderId,
        int $shipmentId,
        ?string $eta,
        ?array $destinationAddress
    ): self {
        return new self(
            orderId: $orderId,
            status: 'in_transit',
            shipmentId: $shipmentId,
            eta: $eta,
            deliveredAt: null,
            canCreate: false,
            nextActionKey: 'deliver',
            nextActionLabel: '配達完了',
            destinationAddress: $destinationAddress,
        );
    }

    public static function delivered(
        int $orderId,
        int $shipmentId,
        ?string $eta,
        ?string $deliveredAt,
        ?array $destinationAddress
    ): self {
        return new self(
            orderId: $orderId,
            status: 'delivered',
            shipmentId: $shipmentId,
            eta: $eta,
            deliveredAt: $deliveredAt,
            canCreate: false,
            nextActionKey: null,
            nextActionLabel: null,
            destinationAddress: $destinationAddress,
        );
    }

    /* ============================
       Output
    ============================ */

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'shipment_id' => $this->shipmentId,
            'status' => $this->status,
            'eta' => $this->eta,
            'delivered_at' => $this->deliveredAt,
            'can_create' => $this->canCreate,
            'next_action' => $this->nextActionKey
                ? [
                    'key' => $this->nextActionKey,
                    'label' => $this->nextActionLabel,
                ]
                : null,
            'destination_address' => $this->destinationAddress,
        ];
    }
}
