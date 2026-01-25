<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\LearningCandidateRepository;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;
use App\Modules\Item\Infrastructure\Persistence\Query\AnalysisResultReadRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Service\EntityFactory;

final class DecideUseCase
{
    public function __construct(
        private AnalysisRequestRepository        $requests,
        private ReviewDecisionRepository         $decisions,
        private ApplyConfirmedDecisionUseCase    $applyConfirmed,
        private LearningCandidateRepository      $learningCandidates,
        private BrandEntityQueryRepository       $brandQuery,
        private ConditionEntityQueryRepository   $conditionQuery,
        private ColorEntityQueryRepository       $colorQuery,
        private EntityFactory                    $entityFactory,
        private AnalysisResultReadRepository     $analysisRepo,
        private AnalysisResultRepository         $results,
    ) {}

    public function handle(
        int $analysisRequestId,
        int $decidedUserId,
        string $decidedByType,
        array $input,
    ): void {

        $decisionType = $input['decision_type'] ?? null;
        if (!is_string($decisionType) || $decisionType === '') {
            throw new DomainException('decision_type is required');
        }

        // v3: UI から来る payload
        $after  = $input['after_snapshot'] ?? null;     // AfterSnapshot (json)
        $before = $input['beforeParsed'] ?? [];         // {brand,color,condition} string map
        $note   = $input['note'] ?? null;

        $resolved = [
            'brand_entity_id'     => null,
            'condition_entity_id' => null,
            'color_entity_id'     => null,
        ];

        DB::transaction(function () use (
            $analysisRequestId,
            $decisionType,
            $decidedUserId,
            $decidedByType,
            $after,
            $before,
            $note,
            $input,
            &$resolved
        ) {
            /** 0) request existence */
            $this->requests->findOrFail($analysisRequestId);

            /** 1) append decision first（最重要・append-only） */
            $this->decisions->appendDecision([
                'analysis_request_id' => $analysisRequestId,
                'decision_type'       => $decisionType,
                'resolved_entities'   => null,
                'before_snapshot'     => null,   // ★ここでは入れない
                'after_snapshot'      => null,
                'note'                => is_string($note) && $note !== '' ? $note : null,
                'decided_by_type'     => $decidedByType,
                'decided_by'          => $decidedUserId,
                'decided_at'          => now(),
            ]);

            /** 2) decisionId 確定（latest） */
            $decision = $this->decisions->findLatestByAnalysisRequestId($analysisRequestId);
            if (!$decision) {
                throw new DomainException('review_decision creation failed');
            }
            $decisionId = (int)$decision->id;

            /* =========================================================
               approve（AI after_snapshot を backend resolve）
               - UI は resolvedEntities を送らない
               - after_snapshot は必須
            ========================================================= */
           if ($decisionType === 'approve') {

    $analysis = $this->analysisRepo->findLatestActiveByRequestId($analysisRequestId);
    if (!is_array($analysis)) {
        throw new DomainException('analysis_result not found for approve');
    }

    $beforeSnapshot = [
        'brand'     => $analysis['brand'] ?? null,
        'condition' => $analysis['condition'] ?? null,
        'color'     => $analysis['color'] ?? null,
    ];
    $afterSnapshot = $beforeSnapshot;

    $this->decisions->updateSnapshots($decisionId, $beforeSnapshot, $afterSnapshot);

    if (!empty($analysis['brand']['name'])) {
    $resolved['brand_entity_id'] =
        $this->brandQuery->resolveCanonicalByName(
            $analysis['brand']['name']
        );

    if ($resolved['brand_entity_id'] === null) {
        throw new DomainException(
            'このブランドは登録されていません。ManualOverride を実行して新規ブランド追加を検討してください。'
        );
    }
    }

    if (!empty($analysis['condition']['name'])) {
        $resolved['condition_entity_id'] = $this->conditionQuery->resolveCanonicalByName($analysis['condition']['name']);
    }
    if (!empty($analysis['color']['name'])) {
        $resolved['color_entity_id'] = $this->colorQuery->resolveCanonicalByName($analysis['color']['name']);
    }
}

            /* =========================================================
               edit_confirm（★絶対に resolve しない）
               - UI が選んだ entity_id をそのまま採用
               - after_snapshot は監査用に必須（UI は canonical_name も同期する）
            ========================================================= */
            if ($decisionType === 'edit_confirm') {

    $inputResolved = $input['resolvedEntities'] ?? null;
    if (!is_array($inputResolved)) {
        throw new DomainException('resolvedEntities is required for edit_confirm');
    }
    if (!is_array($after)) {
        throw new DomainException('after_snapshot is required for edit_confirm');
    }

    // ★ before は AI解析結果で固定（なければ例外でOK）
    $analysis = $this->analysisRepo->findLatestActiveByRequestId($analysisRequestId);
    if (!is_array($analysis)) {
        throw new DomainException('analysis_result not found for edit_confirm');
    }

    $beforeSnapshot = [
        'brand'     => $analysis['brand'] ?? null,
        'condition' => $analysis['condition'] ?? null,
        'color'     => $analysis['color'] ?? null,
    ];

    $this->decisions->updateSnapshots($decisionId, $beforeSnapshot, $after);

    $resolved = [
        'brand_entity_id'     => $inputResolved['brand_entity_id'] ?? null,
        'condition_entity_id' => $inputResolved['condition_entity_id'] ?? null,
        'color_entity_id'     => $inputResolved['color_entity_id'] ?? null,
    ];
}

            /* =========================================================
               manual_override（既存 canonical は resolve、新規だけ作成）
               - after_snapshot 必須
            ========================================================= */
            if ($decisionType === 'manual_override') {

    if (!is_array($after)) {
        throw new DomainException('after_snapshot is required');
    }

    $analysis = $this->analysisRepo->findLatestActiveByRequestId($analysisRequestId);
    if (!is_array($analysis)) {
        throw new DomainException('analysis_result not found for manual_override');
    }

    $beforeSnapshot = [
        'brand'     => $analysis['brand'] ?? null,
        'condition' => $analysis['condition'] ?? null,
        'color'     => $analysis['color'] ?? null,
    ];

    $this->decisions->updateSnapshots($decisionId, $beforeSnapshot, $after);

    foreach (['brand', 'condition', 'color'] as $key) {
        $human = $after[$key]['value'] ?? null;
        if (!is_string($human) || trim($human) === '') continue;

        $existing = match ($key) {
            'brand'     => $this->brandQuery->resolveCanonicalByName($human),
            'condition' => $this->conditionQuery->resolveCanonicalByName($human),
            'color'     => $this->colorQuery->resolveCanonicalByName($human),
        };

        $resolved[$key . '_entity_id'] =
            $existing ?? $this->entityFactory->createCanonicalEntity($key, $human, $human, 'human');
    }
}
            /* =========================================================
               reject（何も resolve しない／SoT 反映もしない）
            ========================================================= */
            if ($decisionType === 'reject') {

    // 監査：何を棄却したか残す（AI結果を before_snapshot に）
    $analysis = $this->analysisRepo->findLatestActiveByRequestId($analysisRequestId);
    if (!is_array($analysis)) {
        // AI結果が無いなら snapshot は入れずに reject だけ残す
        $this->results->markRejectedByRequestId($analysisRequestId);
        return;
    }

    $beforeSnapshot = [
        'brand'     => $analysis['brand'] ?? null,
        'condition' => $analysis['condition'] ?? null,
        'color'     => $analysis['color'] ?? null,
    ];

    // 採用：reject は「解析前入力を採用」なので after_snapshot は beforeParsed を使う
    $afterSnapshot = $this->buildBeforeSnapshotFromBeforeParsed($before);

    $this->decisions->updateSnapshots($decisionId, $beforeSnapshot, $afterSnapshot);

    // ✅ 最重要：AI表示を無効化（ItemDetail は active/provisional を見ている前提）
    $this->results->markRejectedByRequestId($analysisRequestId);

    return;
}

            if ($this->hasNoResolvedEntity($resolved)) {
                Log::warning('[DecideUseCase] no resolved entity', [
                    'decision_id'  => $decisionId,
                    'decisionType' => $decisionType,
                ]);
                return; // decision は残す
            }

            /** 3) resolved_entities 更新（後書き） */
            $this->decisions->updateResolvedEntities($decisionId, $resolved);

            /** 4) learning_candidates */
            foreach (['brand', 'condition', 'color'] as $key) {

                $proposed =
                    $decisionType === 'manual_override'
                        ? ($after[$key]['value'] ?? null)
                        : ($before[$key] ?? ($after[$key]['value'] ?? null));

                $entityId = $resolved[$key . '_entity_id'] ?? null;

                if (!is_string($proposed) || trim($proposed) === '') continue;
                if (!is_int($entityId) && !is_numeric($entityId)) continue;

                $this->learningCandidates->append([
                    'analysis_request_id' => $analysisRequestId,
                    'review_decision_id'  => $decisionId,
                    'entity_type'         => $key,
                    'proposed_name'       => $proposed,
                    'normalized_key'      => mb_strtolower(trim($proposed), 'UTF-8'),
                    'decision_type'       => $decisionType,
                    'entity_id'           => (int)$entityId,
                    'source'              => 'human_review',
                    'confidence'          => $decisionType === 'manual_override' ? 1.0 : null,
                    'status'              => 'pending',
                ]);
            }

            /** 5) SoT 反映（reject は上で return 済み） */
            $this->applyConfirmed->handle($analysisRequestId);
        });
    }

    private function hasNoResolvedEntity(array $resolved): bool
    {
        foreach ($resolved as $id) {
            if ($id !== null) return false;
        }
        return true;
    }

    /**
     * beforeParsed (string map) を before_snapshot (json) にして保存する
     * - 監査/学習の「入力側」を ledger に固定するため
     */
    private function buildBeforeSnapshotFromBeforeParsed(array $beforeParsed): ?array
    {
        $out = [];

        foreach (['brand', 'condition', 'color'] as $k) {
            $v = $beforeParsed[$k] ?? null;
            if (is_string($v) && trim($v) !== '') {
                $out[$k] = [
                    'value' => $v,
                    'source' => 'manual',
                    'confidence' => 1.0,
                ];
            }
        }

        return empty($out) ? null : $out;
    }
}