<?php

namespace App\Modules\Item\Domain\Repository;

interface ItemEntityRepository
{
    public function markAllAsNotLatest(int $itemId): void;

    /** @return int created item_entity id */
    public function create(array $attrs): int;

    public function applyAnalysisResult(
        int $analysisRequestId,
        int $actorUserId,
    ): void;

    public function existsLatestHumanConfirmed(int $itemId, string $version): bool;

}