<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Models\ItemEntity;

final class ApplyConfirmedDecisionUseCase
{
    public function __construct(
        private ReviewDecisionRepository $decisions,
        private AnalysisResultRepository $analysisResults,
    ) {}

    public function handle(int $analysisRequestId): void
    {
        DB::transaction(function () use ($analysisRequestId) {

            /** @var object|null $decision */
            $decision = $this->decisions
                ->findLatestByRequestId($analysisRequestId);

            if (! $decision) {
                return;
            }

            $snapshot = match ($decision->decision_type) {
                'approve'      => $decision->before_snapshot,
                'edit_confirm' => $decision->after_snapshot,
                default        => null,
            };

            if (! is_array($snapshot)) {
                return;
            }

            $analysis = $this->analysisResults
                ->findByRequestId($analysisRequestId);

            if (! $analysis) {
                return;
            }

            // ① 旧 latest を無効化
            ItemEntity::where('item_id', $analysis->itemId())
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ② snapshot から確定値を取り出す
            $brandName     = data_get($snapshot, 'brand.name');
            $conditionName = data_get($snapshot, 'condition.name');
            $colorName     = data_get($snapshot, 'color.name');

            // ③ name → entity_id 解決
            $brandEntityId = $brandName
                ? DB::table('brand_entities')
                    ->where('canonical_name', $brandName)
                    ->value('id')
                : null;

            $conditionEntityId = $conditionName
                ? DB::table('condition_entities')
                    ->where('canonical_name', $conditionName)
                    ->value('id')
                : null;

            $colorEntityId = $colorName
                ? DB::table('color_entities')
                    ->where('canonical_name', $colorName)
                    ->value('id')
                : null;

            // ④ human_confirmed entity 作成（ここが唯一の正）
            ItemEntity::create([
                'item_id'             => $analysis->itemId(),
                'brand_entity_id'     => $brandEntityId,
                'condition_entity_id' => $conditionEntityId,
                'color_entity_id'     => $colorEntityId,

                'is_latest'           => true,
                'source'              => 'human_confirmed',
                'generated_version'   => 'v3_confirmed',
                'generated_at'        => now(),
            ]);
        });
    }
}
