<?php

namespace App\Modules\Review\Domain\ValueObject;

final class ReviewDecisionType
{
    public const CONFIRM = 'confirm';
    public const EDIT_CONFIRM = 'edit_confirm';
    public const REJECT = 'reject';

    public static function assert(string $value): void
    {
        if (!in_array($value, [self::CONFIRM, self::EDIT_CONFIRM, self::REJECT], true)) {
            throw new \InvalidArgumentException("Invalid ReviewDecisionType: {$value}");
        }
    }
}