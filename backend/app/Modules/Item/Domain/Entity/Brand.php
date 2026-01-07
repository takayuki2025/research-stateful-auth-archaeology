<?php

namespace App\Modules\Item\Domain\Entity;

final class Brand
{
    public function __construct(
        public readonly int $id,
        public readonly string $canonicalName,
        public readonly string $displayName,
        public readonly float $confidence = 1.0,
    ) {
    }
}
