<?php

namespace App\Modules\Shop\Domain\ValueObject;

final class ShopCode
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('ShopCode cannot be empty.');
        }

        // 必要ならフォーマット制限も追加可
        // if (!preg_match('/^[a-z0-9\-]+$/', $value)) {
        //     throw new \InvalidArgumentException('Invalid ShopCode format.');
        // }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
