<?php

namespace App\Modules\Item\Domain\Repository;

interface ReviewDecisionRepository
{
    public function save(array $data): void;

    public function findLatestByRequestId(
        int $analysisRequestId
    ): ?object;
}