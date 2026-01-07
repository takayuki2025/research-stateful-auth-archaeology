<?php

namespace App\Modules\Payment\Application\Dto;

final class StartPaymentInput
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $method, // card|konbini
    ) {
    }
}
