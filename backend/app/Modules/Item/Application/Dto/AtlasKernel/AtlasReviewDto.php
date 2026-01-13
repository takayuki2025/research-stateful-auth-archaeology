<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class AtlasReviewDto
{
    public function __construct(
        public readonly int $requestId,
        public readonly string $status,
        public readonly ?float $overallConfidence,
        public readonly array $before,
        public readonly array $after,
        public readonly array $diff,
        public readonly array $attributes,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'overall_confidence' => $this->overallConfidence,
            'before' => $this->before,
            'after' => $this->after,
            'diff' => $this->diff,
            'attributes' => $this->attributes,
        ];
    }
}