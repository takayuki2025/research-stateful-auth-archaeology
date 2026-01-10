<?php

namespace App\Modules\Review\Domain\ValueObject;

final class ReviewStatus
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const NEEDS_RETRY = 'needs_retry';

    public static function assert(string $value): void
    {
        if (!in_array($value, [self::PENDING, self::CONFIRMED, self::NEEDS_RETRY], true)) {
            throw new \InvalidArgumentException("Invalid ReviewStatus: {$value}");
        }
    }
}