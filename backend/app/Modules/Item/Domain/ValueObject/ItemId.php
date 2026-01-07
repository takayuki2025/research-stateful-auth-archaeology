<?php

namespace App\Modules\Item\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

class ItemId
{
    public function __construct(
        private int $value
    ) {
        if ($value <= 0) {
            throw new \InvalidArgumentException('ItemId must be positive');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
