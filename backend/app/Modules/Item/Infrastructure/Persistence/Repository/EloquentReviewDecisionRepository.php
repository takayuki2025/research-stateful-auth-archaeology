<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ReviewDecision;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;

final class EloquentReviewDecisionRepository
    implements ReviewDecisionRepository
{
    // public function append(array $data): int
    // {
    //     $decision = ReviewDecision::create([
    //         'analysis_request_id' => $data['analysis_request_id'],
    //         'subject_type'        => $data['subject_type'] ?? null,
    //         'subject_id'          => $data['subject_id'] ?? null,

    //         'decision_type'       => $data['decision_type'],
    //         'decision_reason'     => $data['decision_reason'] ?? null,
    //         'note'                => $data['note'] ?? null,

    //         'before_snapshot'     => $data['before_snapshot'] ?? null,
    //         'after_snapshot'      => $data['after_snapshot'] ?? null,

    //         'decided_by_type'     => $data['decided_by_type'] ?? 'human',
    //         'decided_by'          => $data['decided_by'] ?? null,
    //         'decided_at'          => $data['decided_at'] ?? now(),
    //     ]);

    //     return $decision->id;
    // }

    public function findLatestByAnalysisRequestId(
        int $analysisRequestId
    ): ?ReviewDecision {
        return ReviewDecision::query()
            ->where('analysis_request_id', $analysisRequestId)
            ->orderByDesc('decided_at')
            ->first();
    }

    public function appendDecision(array $data): void
    {
        ReviewDecision::create($data);
    }
}