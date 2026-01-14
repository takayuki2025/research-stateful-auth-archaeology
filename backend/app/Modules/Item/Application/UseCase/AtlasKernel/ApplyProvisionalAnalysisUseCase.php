<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Models\ItemEntity;
use App\Models\ItemEntityTag;

final class ApplyProvisionalAnalysisUseCase
{
    public function handle(int $itemId, array $analysis): void
    {
        DB::transaction(function () use ($itemId, $analysis) {

            // ① 旧 latest を無効化
            ItemEntity::where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ② name だけ入れる（entity_id は入れない）
            $entity = ItemEntity::create([
                'item_id'         => $itemId,
                'is_latest'       => true,
                'source'          => 'ai_provisional',
                'generated_version'=> 'v3_ai',
                'generated_at'    => now(),

                // ★ 即効 UX 用
                'brand_name'      => data_get($analysis, 'brand.name'),
                'condition_name'  => data_get($analysis, 'condition.name'),
                'color_name'      => data_get($analysis, 'color.name'),

                'confidence'      => data_get($analysis, 'confidence_map'),
            ]);

            // ③ tags（あれば）
            foreach (data_get($analysis, 'tags', []) as $tagType => $tags) {
                foreach ($tags as $tag) {
                    ItemEntityTag::create([
                        'item_entity_id' => $entity->id,
                        'tag_type'       => $tagType,
                        'entity_id'      => null, // ★ provisional では絶対入れない
                        'display_name'   => $tag['display_name'],
                        'confidence'     => $tag['confidence'] ?? null,
                    ]);
                }
            }
        });
    }
}