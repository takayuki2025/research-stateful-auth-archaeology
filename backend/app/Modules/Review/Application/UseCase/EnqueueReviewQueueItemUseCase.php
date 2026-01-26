<?php

namespace App\Modules\Review\Application\UseCase;

use App\Modules\Review\Domain\Repository\ReviewQueueRepository;

final class EnqueueReviewQueueItemUseCase
{
    public function __construct(
        private ReviewQueueRepository $queue,
    ) {
    }

    public function handle(
        ?int $projectId,
        string $queueType,
        string $refType,
        int $refId,
        int $priority = 50,
        ?array $summary = null
    ): int {
        return $this->queue->enqueue($projectId, $queueType, $refType, $refId, $priority, $summary);
    }
}