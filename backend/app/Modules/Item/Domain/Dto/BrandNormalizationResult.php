<?php

namespace App\Modules\Item\Domain\Dto;

final class BrandNormalizationResult
{
    public function __construct(
        public readonly string $canonicalName,
        public readonly float $confidence,
        public readonly ?string $matchedFrom,
    ) {}
}