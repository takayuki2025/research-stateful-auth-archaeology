<?php

namespace App\Modules\Review\Application\UseCase;

use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use App\Modules\Item\Domain\Repository\ItemEntityTagRepository;
use App\Modules\Review\Application\Dto\ReviewDecisionInput;
use App\Modules\Review\Domain\Entity\ReviewDecision;
use App\Modules\Review\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Review\Domain\Repository\ReviewQueryRepository;
use App\Modules\Review\Domain\ValueObject\ReviewDecisionType;
use App\Modules\Review\Domain\ValueObject\ReviewSubject;
use Illuminate\Support\Facades\DB;

final class EditAndConfirmReviewUseCase
{
    public function __construct(
        private ItemEntityRepository $itemEntities,
        private ItemEntityTagRepository $tags,
        private ReviewDecisionRepository $decisions,
        private ReviewQueryRepository $reviewQuery,
    ) {
    }

    /**
     * $editedTags format:
     * [
     *   "brand" => [ ["entity_id"=>1,"display_name"=>"Apple","confidence"=>0.9], ... ],
     *   "condition" => [ ... ],
     *   "color" => [ ... ],
     * ]
     */
    public function handle(int $itemId, array $editedTags, ReviewDecisionInput $input): void
    {
        DB::transaction(function () use ($itemId, $editedTags, $input) {

            $analysis = $this->reviewQuery->getLatestAnalysis($itemId);

            $this->itemEntities->markAllAsNotLatest($itemId);

            $itemEntityId = $this->itemEntities->create([
                'item_id'           => $itemId,
                'confidence'        => $analysis['confidence'] ?? null,
                'generated_version' => $analysis['version'] ?? 'v3',
                'generated_at'      => now(),
                'is_latest'         => true,
            ]);

            foreach ($editedTags as $tagType => $tagRows) {
                $this->tags->replaceTags($itemEntityId, (string)$tagType, (array)$tagRows);
            }

            $decision = new ReviewDecision(
                subject: new ReviewSubject(ReviewSubject::TYPE_ITEM, $itemId),
                decisionType: ReviewDecisionType::EDIT_CONFIRM,
                beforeSnapshot: $analysis,
                afterSnapshot: ['tags' => $editedTags],
                decidedBy: $input->decidedBy,
                note: $input->note,
                decidedAt: new \DateTimeImmutable('now'),
            );

            $this->decisions->save($decision);
        });
    }
}
