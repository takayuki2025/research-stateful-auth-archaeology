<?php

namespace App\Modules\Shipment\Application\Listener;

use App\Modules\Order\Domain\Event\OrderPaid;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Shipment\Domain\Entity\Shipment;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;



final class CreateShipmentOnOrderPaidListener
{
    public function __construct(
        private CreateShipmentDraftUseCase $useCase,
    ) {
    }

    public function handle(OrderPaid $event): void
    {
        $this->useCase->handle(
            new CreateShipmentDraftInput(
                orderId: $event->orderId,
                shopId: $event->shopId,
            )
        );
    }
}
