<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ReviewDecision;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;

final class EloquentReviewDecisionRepository
    implements ReviewDecisionRepository
{
    public function findLatestByAnalysisRequestId(
        int $analysisRequestId
    ): ?ReviewDecision {
        return ReviewDecision::query()
            ->where('analysis_request_id', $analysisRequestId)
            ->orderByDesc('decided_at')
            ->first();
    }

    /**
     * append-only（戻り値なし）
     */
    public function appendDecision(array $data): void
    {
        ReviewDecision::create([
            ...$data,
            'resolved_entities' => $data['resolved_entities'] ?? null,
            'before_snapshot'   => $data['before_snapshot'] ?? null,
            'after_snapshot'    => $data['after_snapshot'] ?? null,
            'decided_at'        => $data['decided_at'] ?? now(),
        ]);
    }
}