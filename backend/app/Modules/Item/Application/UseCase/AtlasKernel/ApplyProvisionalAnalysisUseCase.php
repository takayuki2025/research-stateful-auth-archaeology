<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Models\ItemEntity;
use App\Models\ItemEntityTag;

final class ApplyProvisionalAnalysisUseCase
{
    public function handle(
        int $itemId,
        array $analysisPayload
    ): void {
        DB::transaction(function () use ($itemId, $analysisPayload) {

            // ① 旧 entity を無効化
            ItemEntity::where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ② 仮 entity 作成（v3 正式）
            $entity = ItemEntity::create([
                'item_id'           => $itemId,
                'brand_entity_id'   => $analysisPayload['normalization']['brand_entity_id'] ?? null,
                'is_latest'         => true,
                'generated_version' => 'v3_ai_provisional',
                'generated_at'      => now(),
                'confidence'        => [
                    'brand'     => $analysisPayload['integration']['brand_identity']['confidence'] ?? null,
                    'condition' => 0.8,
                    'color'     => 0.8,
                ],
            ]);

            // ③ tags 展開
            // brand
            if (!empty($analysisPayload['integration']['brand_identity'])) {
                $b = $analysisPayload['integration']['brand_identity'];

                ItemEntityTag::create([
                    'item_entity_id' => $entity->id,
                    'tag_type'       => 'brand',
                    'entity_id'      => $analysisPayload['normalization']['brand_entity_id'] ?? null,
                    'display_name'   => $b['canonical'],
                    'confidence'     => $b['confidence'],
                ]);
            }

            // condition
            foreach ($analysisPayload['extraction']['condition'] ?? [] as $c) {
                ItemEntityTag::create([
                    'item_entity_id' => $entity->id,
                    'tag_type'       => 'condition',
                    'entity_id'      => null,
                    'display_name'   => $c,
                    'confidence'     => 0.8,
                ]);
            }

            // color
            foreach ($analysisPayload['extraction']['color'] ?? [] as $c) {
                ItemEntityTag::create([
                    'item_entity_id' => $entity->id,
                    'tag_type'       => 'color',
                    'entity_id'      => null,
                    'display_name'   => $c,
                    'confidence'     => 0.8,
                ]);
            }
        });
    }
}