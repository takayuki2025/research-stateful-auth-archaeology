<?php

namespace App\Modules\Item\Domain\ValueObject;

final class Brand
{
    public function __construct(
        private string $canonical,
        private float $confidence,
        private ?string $source,
    ) {}

    public function canonical(): string
    {
        return $this->canonical;
    }

    public function confidence(): float
    {
        return $this->confidence;
    }

    public function source(): ?string
    {
        return $this->source;
    }
}
