<?php

namespace App\Modules\Item\Domain\Repository;

interface AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void;
    public function markRejected(int $itemId): void;
    public function markDecided(
        int $itemId,
        string $decidedBy,      // 'human'
        int $decidedUserId
    ): void;
}