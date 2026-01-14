<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Models\ItemEntity;
use App\Models\ItemEntityTag;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;

final class ApplyProvisionalAnalysisUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
    ) {}

    /**
     * v3 固定：
     * - 主語は analysisRequestId のみ
     * - itemId は Request から解決
     * - provisional（暫定）反映専用
     */
    public function handle(
        int $analysisRequestId,
        array $analysis,
    ): void {
        DB::transaction(function () use ($analysisRequestId, $analysis) {

            /**
             * ① Request を SoT として取得
             */
            $request = $this->requests->findOrFail($analysisRequestId);
            $itemId  = $request->itemId();

            /**
             * ② 旧 latest entity を無効化
             */
            ItemEntity::where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            /**
             * ③ provisional entity を作成
             *   - entity_id は絶対に入れない
             *   - UX 即時反映用
             */
            $entity = ItemEntity::create([
                'item_id'           => $itemId,
                'is_latest'         => true,
                'source'            => 'ai_provisional',
                'generated_version' => 'v3_ai',
                'generated_at'      => now(),

                // 表示用（暫定）
                'brand_name'     => data_get($analysis, 'brand.name'),
                'condition_name' => data_get($analysis, 'condition.name'),
                'color_name'     => data_get($analysis, 'color.name'),

                'confidence' => data_get($analysis, 'confidence_map'),
            ]);

            /**
             * ④ provisional tags（存在すれば）
             */
            foreach ((array) data_get($analysis, 'tags', []) as $tagType => $tags) {
                foreach ((array) $tags as $tag) {
                    ItemEntityTag::create([
                        'item_entity_id' => $entity->id,
                        'tag_type'       => $tagType,

                        // v3 固定：provisional では entity_id を入れない
                        'entity_id'      => null,

                        'display_name'   => $tag['display_name'] ?? null,
                        'confidence'     => $tag['confidence'] ?? null,
                    ]);
                }
            }
        });
    }
}