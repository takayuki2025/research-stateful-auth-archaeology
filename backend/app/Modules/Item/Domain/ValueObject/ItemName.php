<?php

namespace App\Modules\Item\Domain\ValueObject;

final class ItemName
{
    public function __construct(
        private string $value
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Item name must not be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}