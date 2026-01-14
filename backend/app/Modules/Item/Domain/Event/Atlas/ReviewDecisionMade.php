<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Event\Atlas;

final class ReviewDecisionMade
{
    public function __construct(
        public readonly int $analysisRequestId
    ) {}
}