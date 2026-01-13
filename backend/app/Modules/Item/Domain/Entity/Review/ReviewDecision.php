<?php

namespace App\Modules\Item\Domain\Entity\Review;

use Carbon\CarbonImmutable;

final class ReviewDecision
{
    public function __construct(
        public readonly int $analysisRequestId,
        public readonly string $decisionType, // approve | edit_confirm | reject | system_approve
        public readonly ?string $decisionReason,
        public readonly ?string $note,
        public readonly ?array $beforeSnapshot,
        public readonly ?array $afterSnapshot,
        public readonly string $decidedByType, // human | system
        public readonly ?int $decidedBy,
        public readonly CarbonImmutable $decidedAt,
        public readonly ?string $subjectType = null,
        public readonly ?int $subjectId = null,
    ) {}
}