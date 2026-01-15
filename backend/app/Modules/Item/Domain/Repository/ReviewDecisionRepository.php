<?php

namespace App\Modules\Item\Domain\Repository;

use App\Models\ReviewDecision;

interface ReviewDecisionRepository
{
    public function save(array $data): void;

    public function appendDecision(array $data): void;

    public function findLatestByAnalysisRequestId(
        int $analysisRequestId
    ): ?ReviewDecision;
}