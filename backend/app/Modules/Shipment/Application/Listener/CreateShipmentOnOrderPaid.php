<?php

// app/Modules/Shipment/Application/Listener/CreateShipmentOnOrderPaid.php

namespace App\Modules\Shipment\Application\Listener;

use App\Modules\Order\Domain\Event\OrderPaid;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Entity\Shipment;
use Illuminate\Support\Facades\Log;

final class CreateShipmentOnOrderPaid
{
    public function __construct(
        private CreateShipmentDraftUseCase $useCase,
    ) {
    }

    public function handle(OrderPaid $event): void
    {


        Log::info('[Shipment] OrderPaid received', [
            'order_id' => $event->orderId,
            'shop_id'  => $event->shopId,
        ]);

        $this->useCase->handle(
            orderId: $event->orderId,
            shopId: $event->shopId,
        );
    }
}
