<?php

namespace App\Modules\Item\Domain\Dto;

final class AtlasAnalysisResult
{
    public function __construct(
        private array $analysis
    ) {}

    public function toArray(): array
    {
        return $this->analysis;
    }
}