<?php

namespace App\Modules\Review\Application\Query;

use App\Modules\Review\Application\Dto\ReviewItemDetailDto;
use App\Modules\Review\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Review\Domain\Repository\ReviewQueryRepository;

final class GetReviewItemDetailQuery
{
    public function __construct(
        private ReviewQueryRepository $repo,
        private ReviewDecisionRepository $decisions,
    ) {
    }

    public function handle(int $itemId): ReviewItemDetailDto
    {
        $detail = $this->repo->getReviewItemDetail($itemId);
        $history = $this->decisions->listBySubject('item', $itemId, 20);

        return new ReviewItemDetailDto(
            itemId: $itemId,
            itemRaw: $detail['item_raw'],
            aiProposal: $detail['ai_proposal'],
            diff: $detail['diff'],
            confidence: $detail['confidence'],
            version: $detail['version'],
            generatedAt: $detail['generated_at'] ?? null,
            decisionHistory: $history,
        );
    }
}