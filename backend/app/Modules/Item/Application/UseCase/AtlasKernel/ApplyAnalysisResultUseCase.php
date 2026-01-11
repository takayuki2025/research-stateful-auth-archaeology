<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Models\ItemEntity;
use App\Models\ItemEntityTag;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

final class ApplyAnalysisResultUseCase
{
    public function __construct(
        private AnalysisResultRepository $analysisRepo
    ) {}

    public function handle(
        int $itemId,
        int $decidedUserId,
        array $finalTags
    ): void {
        DB::transaction(function () use ($itemId, $decidedUserId, $finalTags) {

            // ① 旧 entity を無効化
            ItemEntity::where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ② human 確定 entity 作成（v3 正式）
            $entity = ItemEntity::create([
                'item_id'           => $itemId,
                'is_latest'         => true,
                'generated_version' => 'v3_human_confirmed',
                'generated_at'      => now(),
                'confidence'        => [
                    'source' => 'human',
                ],
            ]);

            // ③ tags 登録（confidence=1.0）
            foreach ($finalTags as $tagType => $tags) {
                foreach ($tags as $tag) {
                    ItemEntityTag::create([
                        'item_entity_id' => $entity->id,
                        'tag_type'       => $tagType,
                        'entity_id'      => $tag['entity_id'] ?? null,
                        'display_name'   => $tag['display_name'],
                        'confidence'     => 1.0,
                    ]);
                }
            }

            // ④ analysis_results を「人が確定」状態に
            $this->analysisRepo->markDecided(
                itemId: $itemId,
                decidedBy: 'human',
                decidedUserId: $decidedUserId
            );
        });
    }
}