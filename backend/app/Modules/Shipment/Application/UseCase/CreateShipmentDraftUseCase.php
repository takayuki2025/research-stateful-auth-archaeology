<?php


namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Application\Dto\CreateShipmentDraftInput;
use App\Modules\Shipment\Domain\Entity\Shipment;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Order\Domain\Repository\OrderRepository;
use Illuminate\Database\QueryException;


final class CreateShipmentDraftUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private ShopRepository $shops,
        private ShipmentRepository $shipments,
    ) {
    }

    public function handle(int $orderId, int $shopId): void
    {
        $order = $this->orders->findById($orderId);
        if (! $order) {
            return;
        }

        $shop = $this->shops->findById($shopId);
        if (! $shop) {
            return;
        }

        if ($this->shipments->existsByOrderId($order->id())) {
            return; // 冪等
        }

        $shipment = Shipment::createDraft(
            shopId: $shop->id(),
            orderId: $order->id(),
            originAddress: $shop->shippingAddress(),
            destinationAddress: $order->shippingAddress(),
        );

        $this->shipments->save($shipment);
    }
}
