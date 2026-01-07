<?php

namespace App\Modules\Item\Domain\ValueObject;

final class Money
{
    public function __construct(
        private int $amount,
        private string $currency
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Price must be >= 0.');
        }
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}