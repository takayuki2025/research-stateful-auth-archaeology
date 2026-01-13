<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Event\Atlas;

final class AtlasReviewEditConfirmed
{
    public function __construct(
        public readonly int $analysisRequestId,
        public readonly int $actorUserId,
        public readonly string $actorRole,
        public readonly array $afterSnapshot,
    ) {}
}