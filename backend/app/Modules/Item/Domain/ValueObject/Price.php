<?php

namespace App\Modules\Item\Domain\ValueObject;


final class Price
{
    public function __construct(
        private int $value
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Price must be >= 0');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
