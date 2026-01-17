<?php

namespace App\Modules\Item\Domain\Repository;

use App\Models\ReviewDecision;

interface ReviewDecisionRepository
{
    public function findLatestByAnalysisRequestId(
        int $analysisRequestId
    ): ?ReviewDecision;

    public function appendDecision(array $data): void;

    public function updateResolvedEntities(
        int $decisionId,
        array $resolved
    ): void;
}