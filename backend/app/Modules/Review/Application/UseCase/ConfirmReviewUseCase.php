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

final class ConfirmReviewUseCase
{
    public function __construct(
        private ItemEntityRepository $itemEntities,
        private ItemEntityTagRepository $tags,
        private ReviewDecisionRepository $decisions,
        private ReviewQueryRepository $reviewQuery,
    ) {
    }

    public function handle(int $itemId, ReviewDecisionInput $input): void
    {
        DB::transaction(function () use ($itemId, $input) {

            $analysis = $this->reviewQuery->getLatestAnalysis($itemId);

            // 既存 latest を落とす
            $this->itemEntities->markAllAsNotLatest($itemId);

            // 新 item_entity を作る
            $itemEntityId = $this->itemEntities->create([
                'item_id'           => $itemId,
                'confidence'        => $analysis['confidence'] ?? null,
                'generated_version' => $analysis['version'] ?? 'v3',
                'generated_at'      => now(),
                'is_latest'         => true,
            ]);

            // tag 保存（AI提案 그대로）
            foreach (($analysis['tags'] ?? []) as $tagType => $tagRows) {
                $this->tags->replaceTags($itemEntityId, (string)$tagType, (array)$tagRows);
            }

            // Decision 保存
            $decision = new ReviewDecision(
                subject: new ReviewSubject(ReviewSubject::TYPE_ITEM, $itemId),
                decisionType: ReviewDecisionType::CONFIRM,
                beforeSnapshot: null,
                afterSnapshot: $analysis,
                decidedBy: $input->decidedBy,
                note: $input->note,
                decidedAt: new \DateTimeImmutable('now'),
            );

            $this->decisions->save($decision);
        });
    }
}