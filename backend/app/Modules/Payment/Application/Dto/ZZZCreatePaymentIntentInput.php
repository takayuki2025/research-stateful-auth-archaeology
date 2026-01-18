<?php

namespace App\Modules\Payment\Application\Dto;

use App\Modules\Payment\Domain\Enum\PaymentMethod;

final class CreatePaymentIntentInput
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $userId,
        public readonly PaymentMethod $method,
    ) {
    }
}
