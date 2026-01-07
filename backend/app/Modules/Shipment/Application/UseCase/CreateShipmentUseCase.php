<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Shipment\Domain\Entity\Shipment;
use App\Modules\Shipment\Domain\Exception\ShipmentAlreadyExistsException;
use App\Modules\Order\Domain\Exception\TenantMismatchException;

final class CreateShipmentUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private ShipmentRepository $shipments,
        private ShopRepository $shops,
    ) {
    }

    public function handle(int $orderId, int $shopId): int
    {

        $order = $this->orders->findById($orderId);
        if (! $order) {
            throw new \DomainException('Order not found');
        }

        if ($order->shopId() !== $shopId) {
            throw new TenantMismatchException();
        }

        if ($this->shipments->existsByOrderId($orderId)) {
            throw new ShipmentAlreadyExistsException();
        }

        $shop = $this->shops->findById($shopId);
        if (! $shop) {
            throw new \DomainException('Shop not found');
        }

        $shipment = Shipment::createDraft(
            shopId: $shopId,
            orderId: $order->id(),
            originAddress: $shop->shippingAddress(),
            destinationAddress: $order->shippingAddress(),
        );

        $saved = $this->shipments->save($shipment);

        return $saved->id();
    }
}
