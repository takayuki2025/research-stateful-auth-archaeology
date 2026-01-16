<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Policy;

final class DecisionPolicy
{
    public function judge(float $confidence): string
    {
        if ($confidence >= 0.90) {
            return 'system_approve';
        }

        if ($confidence >= 0.80) {
            return 'system_approve_notify';
        }

        return 'review_required';
    }
}