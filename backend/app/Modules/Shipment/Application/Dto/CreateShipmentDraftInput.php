<?php

namespace App\Modules\Shipment\Application\Dto;

final class CreateShipmentDraftInput
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $shopId,
    ) {
    }
}
