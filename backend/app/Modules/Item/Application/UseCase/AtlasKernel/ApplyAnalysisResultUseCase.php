<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Models\ItemEntity;
use App\Models\ItemEntityTag;

final class ApplyAnalysisResultUseCase
{
    public function handle(
        int $itemId,
        int $decidedUserId,
        array $finalTags
    ): void {
        DB::transaction(function () use ($itemId, $finalTags) {

            // ① 旧 entity を無効化
            ItemEntity::where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ② 人間確定 entity（SoT）
            $entity = ItemEntity::create([
                'item_id'           => $itemId,
                'is_latest'         => true,
                'generated_version' => 'v3_human_confirmed',
                'generated_at'      => now(),
                'confidence'        => [
                    'source' => 'human',
                ],
            ]);

            // ③ tags（confidence = 1.0）
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

            // ❌ analysis_results は一切触らない（v3ルール）
        });
    }
}
