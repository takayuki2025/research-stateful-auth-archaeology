<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Policy;

use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use DomainException;

final class AtlasDecisionGuard
{
    public function __construct(
        private AtlasReviewQuery $reviewQuery
    ) {}

    public function assertDecidable(
        string $shopCode,
        int $analysisRequestId,
        string $decisionType
    ): void {
        $src = $this->reviewQuery->fetchReviewSource(
            shopCode: $shopCode,
            analysisRequestId: $analysisRequestId
        );

        $latest = $src['latest_decision'] ?? null;

        if (!$latest) {
            return;
        }

        if (in_array($latest['decision_type'], ['approve', 'reject'], true)) {
            throw new DomainException('Already decided.');
        }
    }
}