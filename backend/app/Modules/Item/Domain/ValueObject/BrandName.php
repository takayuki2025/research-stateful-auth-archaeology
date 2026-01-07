<?php

namespace App\Modules\Item\Domain\ValueObject;

final class BrandName
{
    public function __construct(
        private string $value
    ) {
        $value = trim($value);

        if ($value === '') {
            throw new \DomainException('Brand name cannot be empty');
        }

        $this->value = $value;
    }

    /**
     * Domain が保証する正規化済み値
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * 表示用途（将来拡張用）
     */
    public function asString(): string
    {
        return $this->value;
    }
}
