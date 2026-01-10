<?php

namespace App\Modules\Review\Application\Dto;

final class ReviewDecisionInput
{
    public function __construct(
        public readonly int $decidedBy,
        public readonly ?string $note = null,
    ) {
        if ($decidedBy <= 0) {
            throw new \InvalidArgumentException('decidedBy must be positive');
        }
    }
}