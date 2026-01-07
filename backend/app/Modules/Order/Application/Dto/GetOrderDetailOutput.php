<?php

namespace App\Modules\Order\Application\Dto;

use App\Modules\Order\Domain\Entity\Order;
use App\Modules\Payment\Domain\Entity\Payment;
use App\Modules\Shipment\Domain\Entity\Shipment;

final class GetOrderDetailOutput
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $shopId,
        public readonly int $userId,
        public readonly string $orderStatus,
        public readonly int $totalAmount,
        public readonly string $currency,

        // ===== Address Snapshot (Order) =====
        public readonly ?array $shippingAddress,
        public readonly ?string $addressSnapshotAt,

        // ===== Relations =====
        public readonly ?array $payment,
        public readonly ?array $shipment,
    ) {
    }

    public static function from(
        Order $order,
        ?Payment $payment,
        ?Shipment $shipment,
        ?string $deliveredAt, // ★ Event 由来
    ): self {
        return new self(
            orderId: $order->id(),
            shopId: $order->shopId(),
            userId: $order->userId(),
            orderStatus: $order->status()->value,
            totalAmount: $order->totalAmount(),
            currency: $order->currency(),
            shippingAddress: $order->shippingAddress()?->toArray(),
            addressSnapshotAt: $order->addressSnapshotAt()?->format('Y-m-d H:i:s'),
            payment: $payment ? [
                'payment_id' => $payment->id(),
                'provider_payment_id' => $payment->providerPaymentId(),
                'method' => $payment->method()->value,
                'status' => $payment->status()->value,
                'instructions' => $payment->instructions(),
            ] : null,
            shipment: $shipment ? [
                'shipment_id' => $shipment->id(),
                'status'      => $shipment->status()->value,
                'eta'         => $shipment->eta()?->format('Y-m-d'),
                'delivered_at' => $deliveredAt, // ★ 追加
                'address'     => $shipment->destinationAddress()->toArray(),
            ] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'shop_id' => $this->shopId,
            'user_id' => $this->userId,
            'order_status' => $this->orderStatus,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,

            'shipping_address' => $this->shippingAddress,
            'address_snapshot_at' => $this->addressSnapshotAt,

            'payment' => $this->payment,
            'shipment' => $this->shipment,
        ];
    }
}
