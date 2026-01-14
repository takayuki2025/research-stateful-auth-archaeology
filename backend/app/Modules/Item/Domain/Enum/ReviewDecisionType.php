<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Enum;

enum ReviewDecisionType: string
{
    case APPROVE = 'approve';
    case EDIT_CONFIRM = 'edit_confirm';
    case REJECT = 'reject';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'approve' => self::APPROVE,
            'edit_confirm' => self::EDIT_CONFIRM,
            'reject' => self::REJECT,
            default => throw new \InvalidArgumentException("invalid decision_type: {$value}"),
        };
    }

    public function isReject(): bool
    {
        return $this === self::REJECT;
    }

    public function isEditConfirm(): bool
    {
        return $this === self::EDIT_CONFIRM;
    }
}
