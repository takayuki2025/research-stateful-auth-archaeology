<?php

namespace App\Modules\Review\Application\Query;

use App\Modules\Review\Application\Dto\ReviewItemSummaryDto;
use App\Modules\Review\Domain\Repository\ReviewQueryRepository;

final class ListReviewItemsQuery
{
    public function __construct(
        private ReviewQueryRepository $repo,
    ) {
    }

    /** @return array<int, ReviewItemSummaryDto> */
    public function handle(?string $status, ?float $confidenceMin, ?string $analyzedBy, int $limit = 50): array
    {
        $rows = $this->repo->listReviewItems($status, $confidenceMin, $analyzedBy, $limit);

        return array_map(function (array $r) {
            return new ReviewItemSummaryDto(
                itemId: (int)$r['item_id'],
                status: (string)$r['status'],
                confidenceMin: (float)$r['confidence_min'],
                diffCount: (int)$r['diff_count'],
                analyzedBy: (string)$r['analyzed_by'],
                analyzedAt: $r['analyzed_at'] ?? null,
            );
        }, $rows);
    }
}