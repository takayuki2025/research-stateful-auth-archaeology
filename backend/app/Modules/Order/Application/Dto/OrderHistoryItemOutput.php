<?php

namespace App\Modules\Order\Application\Dto;

final class OrderHistoryItemOutput
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $orderStatus,
        public readonly ?string $paymentStatus,
        public readonly ?string $paymentMethod,
        public readonly bool $hasShipment,
    ) {
    }

    public function toArray(): array
    {
        return [
            'order_id'       => $this->orderId,
            'order_status'   => $this->orderStatus,
            'payment_status' => $this->paymentStatus,
            'payment_method' => $this->paymentMethod,
            'has_shipment'   => $this->hasShipment,
        ];
    }
}
