<?php

namespace App\Modules\Search\Domain\Criteria;

final class SortOption
{
    public function __construct(
        public readonly string $field,
        public readonly string $direction = 'desc'
    ) {
    }

    public static function newest(): self
    {
        return new self('created_at', 'desc');
    }

    public static function priceAsc(): self
    {
        return new self('price_amount', 'asc');
    }

    public static function priceDesc(): self
    {
        return new self('price_amount', 'desc');
    }
}
