<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use LogicException;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;

final class ApplyConfirmedDecisionUseCase
{
    public function __construct(
        private ReviewDecisionRepository        $decisions,
        private AnalysisRequestRepository       $requests,
        private ItemEntityRepository            $itemEntities,
        private BrandEntityQueryRepository      $brandQuery,
        private ConditionEntityQueryRepository  $conditionQuery,
        private ColorEntityQueryRepository      $colorQuery,
    ) {}

    public function handle(int $analysisRequestId): void
    {
        DB::transaction(function () use ($analysisRequestId) {

            /** 1) 最新 decision */
            $decision = $this->decisions
                ->findLatestByAnalysisRequestId($analysisRequestId);

            if (!$decision) {
                throw new LogicException('review_decision not found');
            }

            /** 2) 採用系のみ */
            if (!in_array(
                $decision->decision_type,
                ['approve', 'edit_confirm', 'manual_override'],
                true
            )) {
                return;
            }

            /** 3) resolved_entities */
            $resolved = $decision->resolved_entities;
            if (!is_array($resolved)) {
                throw new LogicException('resolved_entities must be array');
            }

            /** 4) canonical 化（brand / condition / color 共通） */
            $resolved['brand_entity_id'] = $this->canonicalId(
                'brand',
                $resolved['brand_entity_id'] ?? null
            );

            $resolved['condition_entity_id'] = $this->canonicalId(
                'condition',
                $resolved['condition_entity_id'] ?? null
            );

            $resolved['color_entity_id'] = $this->canonicalId(
    'color',
    $resolved['color_entity_id'] ?? null
);

// 🔽 fallback：approve/edit_confirm 時のみ
if (
    $resolved['color_entity_id'] === null &&
    in_array($decision->decision_type, ['approve', 'edit_confirm'], true)
) {
    $afterSnapshot = $decision->after_snapshot;

    if (is_array($afterSnapshot) && isset($afterSnapshot['color']['value'])) {
        $resolved['color_entity_id'] =
            $this->colorQuery->resolveCanonicalByName(
                $afterSnapshot['color']['value']
            );
    }
}

            /** 5) request → item */
            $request = $this->requests->findOrFail($analysisRequestId);
            $itemId  = $request->itemId();

            /** 6) 冪等（既に v3_confirmed があれば終了） */
            if ($this->itemEntities->existsLatestHumanConfirmed(
                $itemId,
                'v3_confirmed'
            )) {
                return;
            }

            /** 7) latest 無効化 */
            $this->itemEntities->markAllAsNotLatest($itemId);

            /** 8) human_confirmed 作成（SoT） */
            $this->itemEntities->create([
                'item_id'             => $itemId,
                'brand_entity_id'     => $resolved['brand_entity_id'],
                'condition_entity_id' => $resolved['condition_entity_id'],
                'color_entity_id'     => $resolved['color_entity_id'],
                'source'              => 'human_confirmed',
                'is_latest'           => true,
                'generated_version'   => 'v3_confirmed',
                'generated_at'        => now(),
            ]);
        });
    }

    /**
     * 種別ごとの canonical 解決（共通化）
     */
    private function canonicalId(string $type, ?int $entityId): ?int
    {
        if ($entityId === null) {
            return null;
        }

        return match ($type) {
            'brand'     => $this->brandQuery->resolveCanonicalByEntityId($entityId),
            'condition' => $this->conditionQuery->resolveCanonicalByEntityId($entityId),
            'color'     => $this->colorQuery->resolveCanonicalByEntityId($entityId),
            default     => throw new LogicException("Unknown entity type: {$type}"),
        };
    }
}