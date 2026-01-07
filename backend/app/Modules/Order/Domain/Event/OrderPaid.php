<?php

namespace App\Modules\Order\Domain\Event;

final class OrderPaid
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $shopId
    ) {
    }
}
