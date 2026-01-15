<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Models\ItemEntity;

final class ApplyConfirmedDecisionUseCase
{
    public function __construct(
        private ReviewDecisionRepository $decisions,
        private AnalysisResultRepository $analysisResults,
        private AnalysisRequestRepository $analysisRequests, // ★ 必須
    ) {}

    public function handle(int $analysisRequestId): void
    {
        Log::info('[ApplyConfirmedDecision] handle start', [
            'request_id' => $analysisRequestId,
        ]);

        DB::transaction(function () use ($analysisRequestId) {

            /** 1️⃣ 最新 decision */
            $decision = $this->decisions
                ->findLatestByAnalysisRequestId($analysisRequestId);

            if (! $decision) {
                Log::warning('[ApplyConfirmedDecision] decision not found');
                return;
            }

            /** 2️⃣ snapshot 選択 */
            $snapshot = match ($decision->decision_type) {
                'approve'      => $decision->before_snapshot,
                'edit_confirm' => $decision->after_snapshot,
                default        => null,
            };

            if (! is_array($snapshot)) {
                Log::warning('[ApplyConfirmedDecision] snapshot invalid');
                return;
            }

            /** 3️⃣ analysis_request → item_id 解決（ここが正） */
            $analysisRequest = $this->analysisRequests
                ->findOrFail($analysisRequestId);

            if (! $analysisRequest) {
                Log::error('[ApplyConfirmedDecision] analysis_request not found');
                return;
            }

            $itemId = $analysisRequest->itemId();

            Log::info('[ApplyConfirmedDecision] resolved item_id', [
                'item_id' => $itemId,
            ]);

            /** 4️⃣ 既存 latest 無効化 */
            ItemEntity::where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            /** 5️⃣ snapshot.value を正しく読む（超重要） */
            $brandName     = $snapshot['brand']['value']     ?? null;
            $conditionName = $snapshot['condition']['value'] ?? null;
            $colorName     = $snapshot['color']['value']     ?? null;

            Log::info('[ApplyConfirmedDecision] snapshot values', [
                'brand' => $brandName,
                'condition' => $conditionName,
                'color' => $colorName,
            ]);

            /** 6️⃣ entity 解決 */
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

            Log::info('[ApplyConfirmedDecision] resolved entity ids', [
                'brand_entity_id' => $brandEntityId,
                'condition_entity_id' => $conditionEntityId,
                'color_entity_id' => $colorEntityId,
            ]);

            /** 7️⃣ human_confirmed 作成（唯一の SoT） */
            ItemEntity::create([
                'item_id'             => $itemId,
                'brand_entity_id'     => $brandEntityId,
                'condition_entity_id' => $conditionEntityId,
                'color_entity_id'     => $colorEntityId,
                'is_latest'           => true,
                'source'              => 'human_confirmed',
                'generated_version'   => 'v3_confirmed',
                'generated_at'        => now(),
            ]);

            Log::info('[ApplyConfirmedDecision] ItemEntity created');
        });
    }
}