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

            /*
|--------------------------------------------------------------------------
| 1) latest decision
|--------------------------------------------------------------------------
*/
$decision = $this->decisions
    ->findLatestByAnalysisRequestId($analysisRequestId);

if (!$decision) {
    throw new LogicException('review_decision not found');
}

/*
|--------------------------------------------------------------------------
| 2) decision_type branch（★最優先）
|--------------------------------------------------------------------------
*/
if ($decision->decision_type === 'reject') {
    // ★ reject は SoT に一切触れない
    return;
}

if (!in_array(
    $decision->decision_type,
    ['approve', 'edit_confirm', 'manual_override'],
    true
)) {
    throw new LogicException(
        'Unsupported decision_type: ' . $decision->decision_type
    );
}

/*
|--------------------------------------------------------------------------
| 3) resolved_entities（reject を除外した後で検証）
|--------------------------------------------------------------------------
*/
$resolved = $decision->resolved_entities;

if (!is_array($resolved)) {
    throw new LogicException('resolved_entities must be array');
}

            /*
            |--------------------------------------------------------------------------
            | 4) canonical resolve（entity_id → canonical_id）
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | 4.5) color fallback（approve / edit_confirm のみ）
            |--------------------------------------------------------------------------
            */
            if (
                $resolved['color_entity_id'] === null &&
                in_array($decision->decision_type, ['approve', 'edit_confirm'], true)
            ) {
                $after = $decision->after_snapshot;

                if (is_array($after) && isset($after['color']['value'])) {
                    $resolved['color_entity_id'] = $this->colorQuery->resolveCanonicalByName(
                        (string)$after['color']['value']
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 5) request → item
            |--------------------------------------------------------------------------
            */
            $request = $this->requests->findOrFail($analysisRequestId);
            $itemId  = $request->itemId();

            /*
            |--------------------------------------------------------------------------
            | 6) idempotency（★decision 単位）
            |--------------------------------------------------------------------------
            | 同じ decision を二回適用しない。
            | edit_confirm / manual_override は「別 decision」なので何度でも反映される。
            */
            if ($this->itemEntities->existsByDecisionId((int)$decision->id)) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 7) latest 無効化（item の最新版を差し替える）
            |--------------------------------------------------------------------------
            */
            $this->itemEntities->markAllAsNotLatest($itemId);

            /*
            |--------------------------------------------------------------------------
            | 8) human_confirmed 作成（SoT）
            |--------------------------------------------------------------------------
            | review_decision_id を保存して「どの decision が SoT を作ったか」を追跡する。
            */
            $this->itemEntities->create([
                'item_id'             => $itemId,
                'brand_entity_id'      => $resolved['brand_entity_id'],
                'condition_entity_id'  => $resolved['condition_entity_id'],
                'color_entity_id'      => $resolved['color_entity_id'],
                'source'               => 'human_confirmed',
                'is_latest'            => true,
                'generated_version'    => 'v3_confirmed',
                'generated_at'         => now(),
                'review_decision_id'   => (int)$decision->id,   // ★追加（decision 単位冪等のキー）
                'analysis_request_id'  => $analysisRequestId,    // ★任意：追跡を強化するなら推奨
            ]);
        });
    }

    /**
     * 種別ごとの canonical 解決
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