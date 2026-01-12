<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class DecideAnalysisRequestInput
{
    public function __construct(
        public readonly int $requestId,
        public readonly string $decision,   // 'approved' | 'rejected'
        public readonly ?string $reason,
        public readonly int $decidedUserId,
        public readonly string $decidedBy = 'human',
    ) {
    }
}