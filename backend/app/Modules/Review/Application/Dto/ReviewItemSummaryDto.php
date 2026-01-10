<?php

namespace App\Modules\Review\Application\Dto;

final class ReviewItemSummaryDto
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $status,
        public readonly float $confidenceMin,
        public readonly int $diffCount,
        public readonly string $analyzedBy,
        public readonly ?string $analyzedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'item_id'        => $this->itemId,
            'status'         => $this->status,
            'confidence_min' => $this->confidenceMin,
            'diff_count'     => $this->diffCount,
            'analyzed_by'    => $this->analyzedBy,
            'analyzed_at'    => $this->analyzedAt,
        ];
    }
}