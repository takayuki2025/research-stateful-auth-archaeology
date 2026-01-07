<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Domain\Entity\Order;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Order\Application\Dto\OrderHistoryItemOutput;

final class GetOrderHistoryUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentRepository $payments,
        private ShipmentRepository $shipments,
    ) {
    }

    /**
     * @return OrderHistoryItemOutput[]
     */
    public function handle(int $userId): array
    {
        $orders = $this->orders->findByBuyer($userId);

        return array_map(
            fn (Order $order) => $this->mapOne($order),
            $orders
        );
    }

    private function mapOne(Order $order): OrderHistoryItemOutput
    {
        $orderId = (int)($order->id() ?? 0);
        if ($orderId <= 0) {
            // 永続化済みのみ一覧に出る想定だが、念のため防御
            return new OrderHistoryItemOutput(
                orderId: 0,
                orderStatus: $order->status()->value,
                paymentStatus: null,
                paymentMethod: null,
                hasShipment: false,
            );
        }

        $payment  = $this->payments->findLatestByOrderId($orderId);
        $shipment = $this->shipments->findByOrderId($orderId);

        return new OrderHistoryItemOutput(
            orderId: $orderId,
            orderStatus: $order->status()->value,
            paymentStatus: $payment?->status()->value,
            paymentMethod: $payment?->method()->value,
            hasShipment: $shipment !== null,
        );
    }
}
