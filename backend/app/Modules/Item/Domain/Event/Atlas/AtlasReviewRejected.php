<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Event\Atlas;

final class AtlasReviewRejected
{
    public function __construct(
        public readonly int $analysisRequestId,
        public readonly int $actorUserId,
        public readonly string $actorRole,
        public readonly ?string $note,
    ) {}
}