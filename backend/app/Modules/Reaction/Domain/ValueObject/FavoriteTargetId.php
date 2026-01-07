<?php

namespace App\Modules\Reaction\Domain\ValueObject;

final class FavoriteTargetId
{
    public function __construct(private readonly int $itemId)
    {
        if ($itemId <= 0) {
            throw new \InvalidArgumentException('FavoriteTargetId must be positive.');
        }
    }

    public function value(): int
    {
        return $this->itemId;
    }
}
