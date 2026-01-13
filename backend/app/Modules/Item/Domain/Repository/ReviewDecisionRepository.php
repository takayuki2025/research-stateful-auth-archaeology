<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

interface ReviewDecisionRepository
{
    public function appendDecision(
        int $analysisRequestId,
        string $decisionType,
        ?array $beforeSnapshot,
        ?array $afterSnapshot,
        ?string $note,
        int $actorUserId,
        string $actorRole,
    ): void;

    public function latestDecisionType(int $analysisRequestId): ?string;
}