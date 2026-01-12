<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Entity;

use Carbon\CarbonImmutable;

final class DecisionLedger
{
    public function __construct(
        public readonly int $id,
        public readonly int $analysisRequestId,
        public readonly int $decidedUserId,
        public readonly string $decidedBy,   // 'human'
        public readonly string $decision,    // 'approved' | 'rejected'
        public readonly ?string $reason,
        public readonly CarbonImmutable $decidedAt,
    ) {
    }
}