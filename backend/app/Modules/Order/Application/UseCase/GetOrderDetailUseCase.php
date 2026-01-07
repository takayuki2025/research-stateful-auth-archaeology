<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Application\Dto\GetOrderDetailOutput;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventQueryRepository;
use DomainException;
use App\Modules\Shipment\Domain\Repository\ShipmentEventReadRepository;
use App\Modules\Shipment\Infrastructure\Persistence\Query\DbShipmentEventQueryRepository;


final class GetOrderDetailUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentRepository $payments,
        private ShipmentRepository $shipments,
        private ShipmentEventReadRepository $shipmentEventReads,
    ) {
    }

    public function handle(int $orderId, int $userId): GetOrderDetailOutput
    {
        $order = $this->orders->findById($orderId);
        if (! $order) {
            throw new DomainException('Order not found');
        }

        if ($order->userId() !== $userId) {
            throw new DomainException('Forbidden');
        }

        $payment  = $this->payments->findLatestByOrderId($orderId);
        $shipment = $this->shipments->findByOrderId($orderId);

        $deliveredAt = null;
        if ($shipment) {
            $deliveredAt = $this->shipmentEventReads
                ->findDeliveredAtByShipmentId($shipment->id());
        }

        return GetOrderDetailOutput::from(
            order: $order,
            payment: $payment,
            shipment: $shipment,
            deliveredAt: $deliveredAt,
        );
    }
}