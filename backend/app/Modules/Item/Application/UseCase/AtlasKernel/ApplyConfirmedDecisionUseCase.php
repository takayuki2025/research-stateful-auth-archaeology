<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($analysisRequestId) {

            /** 1) 最新 decision */
            $decision = $this->decisions
                ->findLatestByAnalysisRequestId($analysisRequestId);

            if (!$decision) {
                throw new \RuntimeException('review_decision not found');
            }

            /** 2) 採用系のみ */
            if (!in_array(
                $decision->decision_type,
                ['approve', 'edit_confirm', 'manual_override'],
                true
            )) {
                return;
            }

            $resolved = $decision->resolved_entities;

if (!is_array($resolved)) {
    throw new \LogicException('resolved_entities must be array');
}

$brandEntityId     = $resolved['brand_entity_id'] ?? null;
$conditionEntityId = $resolved['condition_entity_id'] ?? null;
$colorEntityId     = $resolved['color_entity_id'] ?? null;

// Decide で保証されているので、ここは assert 的扱い
foreach (['brand_entity_id', 'condition_entity_id', 'color_entity_id'] as $key) {
    if (!array_key_exists($key, $resolved)) {
        throw new \LogicException("resolved_entities.$key is required");
    }
}

            /** 4) request → item */
            $request = $this->requests->findOrFail($analysisRequestId);
            $itemId  = $request->itemId();

            /** 5) 冪等 */
            if ($this->itemEntities->existsLatestHumanConfirmed(
                $itemId,
                'v3_confirmed'
            )) {
                return;
            }

            /** 6) latest 無効化 */
            $this->itemEntities->markAllAsNotLatest($itemId);
\Log::info('[ApplyConfirmedDecision] resolved', [
    'resolved' => $resolved,
]);
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
        });
    }
}
