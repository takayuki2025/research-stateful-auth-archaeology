<?php

namespace App\Modules\Review\Domain\ValueObject;

final class ReviewSubject
{
    public const TYPE_ITEM = 'item';

    public function __construct(
        public readonly string $subjectType,
        public readonly int $subjectId,
    ) {
        if ($subjectType !== self::TYPE_ITEM) {
            throw new \InvalidArgumentException("Unsupported subjectType: {$subjectType}");
        }
        if ($subjectId <= 0) {
            throw new \InvalidArgumentException("Invalid subjectId: {$subjectId}");
        }
    }
}