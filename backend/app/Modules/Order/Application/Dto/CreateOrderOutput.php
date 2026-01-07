<?php

namespace App\Modules\Order\Application\Dto;

use App\Modules\Order\Domain\Enum\OrderStatus;

final class CreateOrderOutput
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $status,
        public readonly int $totalAmount,
        public readonly string $currency,
    ) {
    }

    public function toArray(): array
    {
        return [
            'order_id'     => $this->orderId,
            'status'       => $this->status,
            'total_amount' => $this->totalAmount,
            'currency'     => $this->currency,
        ];
    }

    public static function from(int $orderId, OrderStatus $status, int $totalAmount, string $currency): self
    {
        return new self($orderId, $status->value, $totalAmount, $currency);
    }
}
