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

        $after  = $input['after_snapshot'] ?? null;
        $before = $input['beforeParsed'] ?? [];

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
            &$resolved
        ) {

            /** 0) request existence */
            $this->requests->findOrFail($analysisRequestId);

            /** 1) ★必ず decision を先に append（ここが最重要） */
            $this->decisions->appendDecision([
                'analysis_request_id' => $analysisRequestId,
                'decision_type'       => $decisionType,
                'resolved_entities'   => null, // 一旦 null
                'after_snapshot'      => is_array($after) ? $after : null,
                'note'                => null,
                'decided_by_type'     => $decidedByType,
                'decided_by'          => $decidedUserId,
                'decided_at'          => now(),
            ]);

            /** 2) decisionId 確定 */
            $decision = $this->decisions
                ->findLatestByAnalysisRequestId($analysisRequestId);

            if (!$decision) {
                throw new DomainException('review_decision creation failed');
            }

            $decisionId = $decision->id;

            /** ----------------------------------------------------------------
             * approve / edit_confirm
             * ---------------------------------------------------------------- */
            if (in_array($decisionType, ['approve', 'edit_confirm'], true)) {

                if (!is_array($after)) {
                    throw new DomainException('after_snapshot is required');
                }

                foreach ([
                    'brand'     => $this->brandQuery,
                    'condition' => $this->conditionQuery,
                ] as $key => $repo) {

                    $value = $after[$key]['value'] ?? null;
                    if (!$value) continue;

                    $id = $repo->resolveCanonicalByName($value);
                    if ($id === null) {
                        throw new DomainException(
                            ucfirst($key) . ' must be selected from existing canonical.'
                        );
                    }
                    $resolved[$key . '_entity_id'] = $id;
                }

                if (!empty($after['color']['value'])) {
                    $resolved['color_entity_id'] =
                        $this->colorQuery->resolveCanonicalByName(
                            $after['color']['value']
                        );
                }
            }

            /** ----------------------------------------------------------------
             * manual_override（既存 canonical は resolve、新規だけ作成）
             * ---------------------------------------------------------------- */
            if ($decisionType === 'manual_override') {

                if (!is_array($after)) {
                    throw new DomainException('after_snapshot is required');
                }

                foreach (['brand', 'condition', 'color'] as $key) {

                    $human = $after[$key]['value'] ?? null;
                    if (!$human) continue;

                    $existing = match ($key) {
                        'brand'     => $this->brandQuery->resolveCanonicalByName($human),
                        'condition' => $this->conditionQuery->resolveCanonicalByName($human),
                        'color'     => $this->colorQuery->resolveCanonicalByName($human),
                    };

                    $resolved[$key . '_entity_id'] =
                        $existing
                        ?? $this->entityFactory->createCanonicalEntity(
                            $key,
                            $human,
                            $human,
                            'human'
                        );
                }
            }

            if ($this->hasNoResolvedEntity($resolved)) {
                Log::warning('[DecideUseCase] no resolved entity', [
                    'decision_id' => $decisionId,
                    'decisionType' => $decisionType,
                ]);
                return; // decision は残す
            }

            /** 3) resolved_entities 更新（後書き） */
            $this->decisions->updateResolvedEntities(
                $decisionId,
                $resolved
            );

            /** 4) learning_candidates */
            foreach (['brand', 'condition', 'color'] as $key) {

                $proposed =
                    $decisionType === 'manual_override'
                        ? ($after[$key]['value'] ?? null)
                        : ($before[$key] ?? ($after[$key]['value'] ?? null));

                $entityId = $resolved[$key . '_entity_id'] ?? null;

                if (!$proposed || !$entityId) continue;

                $this->learningCandidates->append([
                    'analysis_request_id' => $analysisRequestId,
                    'review_decision_id'  => $decisionId,
                    'entity_type'         => $key,
                    'proposed_name'       => $proposed,
                    'normalized_key'      => mb_strtolower(trim($proposed), 'UTF-8'),
                    'decision_type'       => $decisionType,
                    'entity_id'           => $entityId,
                    'source'              => 'human_review',
                    'confidence'          => $decisionType === 'manual_override' ? 1.0 : null,
                    'status'              => 'pending',
                ]);
            }

            /** 5) SoT 反映（reject は何もしない） */
            if (in_array($decisionType, ['approve', 'edit_confirm', 'manual_override'], true)) {
                $this->applyConfirmed->handle($analysisRequestId);
            }
        });
    }

    private function hasNoResolvedEntity(array $resolved): bool
    {
        foreach ($resolved as $id) {
            if ($id !== null) return false;
        }
        return true;
    }
}