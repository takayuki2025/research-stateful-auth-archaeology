<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ReviewDecision;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;

final class EloquentReviewDecisionRepository implements ReviewDecisionRepository
{
    public function findLatestByAnalysisRequestId(int $analysisRequestId): ?ReviewDecision
    {
        return ReviewDecision::query()
            ->where('analysis_request_id', $analysisRequestId)
            ->orderByDesc('decided_at')
            ->orderByDesc('id') // ★同時刻でも安定
            ->first();
    }

    /**
     * append-only（戻り値なし）
     */
    public function appendDecision(array $data): void
    {
        ReviewDecision::create([
            ...$data,

            // ★ JSONはここで確実に載せる（落ちていた分）
            'resolved_entities' => $data['resolved_entities'] ?? null,
            'before_snapshot'   => $data['before_snapshot'] ?? null,
            'after_snapshot'    => $data['after_snapshot'] ?? null,

            'note'              => $data['note'] ?? null,
            'decided_at'        => $data['decided_at'] ?? now(),
        ]);
    }

    public function updateResolvedEntities(int $decisionId, array $resolved): void
    {
        ReviewDecision::where('id', $decisionId)->update([
            'resolved_entities' => $resolved,
            'updated_at'        => now(),
        ]);
    }

    public function updateSnapshots(
    int $decisionId,
    array $before,
    array $after
): void {
    ReviewDecision::where('id', $decisionId)->update([
        'before_snapshot' => $before,
        'after_snapshot'  => $after,
        'updated_at'      => now(),
    ]);
}
}