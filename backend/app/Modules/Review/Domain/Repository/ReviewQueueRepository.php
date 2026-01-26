<?php

namespace App\Modules\Review\Domain\Repository;

interface ReviewQueueRepository
{
    /**
     * Create pending item if not exists.
     * Returns queue item id.
     */
    public function enqueue(
        ?int $projectId,
        string $queueType,
        string $refType,
        int $refId,
        int $priority,
        ?array $summary
    ): int;

    /** @return array<int, array> */
    public function list(
        ?string $queueType,
        ?string $status,
        int $limit,
        int $offset
    ): array;

    public function get(int $id): ?array;

    /**
     * Decide action for an item (approve/reject/request_more_info)
     */
    public function decide(
        int $id,
        string $action,
        ?int $decidedBy,
        ?string $note,
        ?array $extra
    ): void;

    public function updateStatus(int $id, string $status): void;

    public function clearDecision(int $id): void;
}
