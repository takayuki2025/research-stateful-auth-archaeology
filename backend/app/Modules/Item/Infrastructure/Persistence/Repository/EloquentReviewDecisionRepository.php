<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ReviewDecision;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;

final class EloquentReviewDecisionRepository
    implements ReviewDecisionRepository
{
    public function save(array $data): void
    {
        ReviewDecision::create($data);
    }

    public function appendDecision(array $data): void
    {
        ReviewDecision::create($data);
    }

    public function findLatestByAnalysisRequestId(
        int $analysisRequestId
    ): ?ReviewDecision {
        return ReviewDecision::query()
            ->where('analysis_request_id', $analysisRequestId)
            ->orderByDesc('decided_at')
            ->first();
    }

}