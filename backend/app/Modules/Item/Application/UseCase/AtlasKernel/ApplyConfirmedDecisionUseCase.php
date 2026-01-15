<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\ItemEntityRepository;

final class ApplyConfirmedDecisionUseCase
{
    public function __construct(
        private ReviewDecisionRepository  $decisions,
        private AnalysisRequestRepository $requests,
        private ItemEntityRepository      $itemEntities,
    ) {}

    public function handle(int $analysisRequestId): void
    {
        Log::info('[🔥ApplyConfirmedDecision] START', [
            'analysis_request_id' => $analysisRequestId,
        ]);

        DB::transaction(function () use ($analysisRequestId) {

            /** 1) decision 取得 */
            $decision = $this->decisions
                ->findLatestByAnalysisRequestId($analysisRequestId);

            if (! $decision) {
                Log::warning('[ApplyConfirmedDecision] decision not found');
                return;
            }

            /** 2) approve / edit_confirm のみ適用 */
            if (! in_array($decision->decision_type, ['approve', 'edit_confirm'], true)) {
                Log::info('[ApplyConfirmedDecision] decision rejected. skip apply.');
                return;
            }

            /** 3) after_snapshot（entity_id 前提） */
            $snapshot = $decision->after_snapshot;

            if (! is_array($snapshot)) {
                Log::error('[ApplyConfirmedDecision] snapshot invalid');
                return;
            }

            $brandEntityId     = $snapshot['brand_entity_id']     ?? null;
            $conditionEntityId = $snapshot['condition_entity_id'] ?? null;
            $colorEntityId     = $snapshot['color_entity_id']     ?? null;

            if (! $brandEntityId && ! $conditionEntityId && ! $colorEntityId) {
                Log::warning('[ApplyConfirmedDecision] no entity ids. skip.');
                return;
            }

            /** 4) request → item */
            $request = $this->requests->findOrFail($analysisRequestId);
            $itemId  = $request->itemId();

            /** 5) 冪等 */
            if ($this->itemEntities->existsLatestHumanConfirmed($itemId, 'v3_confirmed')) {
                Log::info('[ApplyConfirmedDecision] already applied. skip.');
                return;
            }

            /** 6) latest 無効化 */
            $this->itemEntities->markAllAsNotLatest($itemId);

            /** 7) human_confirmed 作成 */
            $this->itemEntities->create([
                'item_id'             => $itemId,
                'brand_entity_id'     => $brandEntityId,
                'condition_entity_id' => $conditionEntityId,
                'color_entity_id'     => $colorEntityId,
                'source'              => 'human_confirmed',
                'is_latest'           => true,
                'generated_version'   => 'v3_confirmed',
                'generated_at'        => now(),
            ]);

            Log::info('[🔥ApplyConfirmedDecision] DONE', [
                'item_id' => $itemId,
            ]);
        });
    }
}