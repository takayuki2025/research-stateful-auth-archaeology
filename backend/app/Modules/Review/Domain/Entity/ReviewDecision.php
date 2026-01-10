<?php

namespace App\Modules\Review\Domain\Entity;

use App\Modules\Review\Domain\ValueObject\ReviewDecisionType;
use App\Modules\Review\Domain\ValueObject\ReviewSubject;

final class ReviewDecision
{
    public function __construct(
        public readonly ReviewSubject $subject,
        public readonly string $decisionType,
        public readonly ?array $beforeSnapshot,
        public readonly ?array $afterSnapshot,
        public readonly int $decidedBy,
        public readonly ?string $note,
        public readonly \DateTimeImmutable $decidedAt,
    ) {
        ReviewDecisionType::assert($decisionType);
        if ($decidedBy <= 0) {
            throw new \InvalidArgumentException('decidedBy must be positive');
        }
    }

    public function toArray(): array
    {
        return [
            'subject_type'    => $this->subject->subjectType,
            'subject_id'      => $this->subject->subjectId,
            'decision_type'   => $this->decisionType,
            'before_snapshot' => $this->beforeSnapshot,
            'after_snapshot'  => $this->afterSnapshot,
            'decided_by'      => $this->decidedBy,
            'note'            => $this->note,
            'decided_at'      => $this->decidedAt->format('Y-m-d H:i:s'),
        ];
    }
}