<?php

namespace App\Modules\Review\Domain\Repository;

interface ReviewRequestForInfoRepository
{
    /**
     * Create new open request for info.
     */
    public function open(int $reviewQueueItemId, array $checklist, ?int $requestedBy): int;

    /**
     * Close all open requests for a queue item (on approve/reject, optional).
     */
    public function closeOpenByQueueItem(int $reviewQueueItemId, ?int $closedBy): void;

    /** @return array<int, array> */
    public function listByQueueItem(int $reviewQueueItemId): array;
}