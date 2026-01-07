<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Shipment\Infrastructure\Persistence\Query\DbShipmentQueryRepository;
use App\Modules\Order\Application\Dto\GetOrderDetailOutput;

final class GetMyOrderShipmentUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private DbShipmentQueryRepository $shipments,
    ) {
    }

    public function handle(int $userId, int $orderId): ?array
    {
        $order = $this->orders->findById($orderId);

        if (! $order) {
            return null;
        }

        // 🔒 自分の注文かチェック
        if ($order->userId() !== $userId) {
            return null;
        }

        $row = $this->shipments->findByOrderId($orderId);

        if (! $row) {
            return null;
        }


        $deliveredAt = $this->shipmentQuery->findDeliveredAtByShipmentId($shipment?->id());

        return GetOrderDetailOutput::from(
            $order,
            $payment,
            $shipment,
            $deliveredAt,
        );

    }
}
