<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ReviewDecision;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use Carbon\CarbonImmutable;

final class EloquentReviewDecisionRepository implements ReviewDecisionRepository
{
    public function appendDecision(
        int $analysisRequestId,
        string $decisionType,
        ?array $beforeSnapshot,
        ?array $afterSnapshot,
        ?string $note,
        int $actorUserId,
        string $actorRole,
    ): void {
        ReviewDecision::create([
            'analysis_request_id' => $analysisRequestId,
            'decision_type' => $decisionType,
            'decision_reason' => null,
            'note' => $note,
            'before_snapshot' => $beforeSnapshot,
            'after_snapshot' => $afterSnapshot,
            'decided_by_type' => 'human',
            'decided_by' => $actorUserId,
            'decided_at' => CarbonImmutable::now(),
        ]);
    }
}