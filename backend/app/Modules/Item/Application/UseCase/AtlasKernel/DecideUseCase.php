<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\LearningCandidateRepository;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Domain\Service\EntityFactory;
// use App\Models\BrandEntity;
// use App\Models\ConditionEntity;
// use App\Models\ColorEntity;


final class DecideUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private ReviewDecisionRepository $decisions,
        private ApplyConfirmedDecisionUseCase $applyConfirmed,
        private LearningCandidateRepository $learningCandidates,
        private BrandEntityQueryRepository $brandQuery,
        private EntityFactory $entityFactory,
    ) {}

    public function handle(
        int $analysisRequestId,
        int $decidedUserId,
        string $decidedByType,
        array $input,
    ): void {
        Log::info('[DecideUseCase] entered', [
            'analysisRequestId' => $analysisRequestId,
            'decidedUserId'     => $decidedUserId,
            'decidedByType'     => $decidedByType,
            'keys'              => array_keys($input),
            'decision_type'     => $input['decision_type'] ?? null,
        ]);

        $decisionType = $input['decision_type'] ?? null;
        if (!is_string($decisionType) || $decisionType === '') {
            throw new \LogicException('decision_type is required');
        }

        // approve/edit_confirm/manual_override は resolvedEntities 必須
        if (in_array($decisionType, ['approve', 'edit_confirm', 'manual_override'], true)) {
            $resolved = $input['resolvedEntities'] ?? null;
            if (!is_array($resolved)) {
                throw new \LogicException('resolvedEntities must be array');
            }

            foreach (['brand_entity_id', 'condition_entity_id', 'color_entity_id'] as $key) {
                if (!array_key_exists($key, $resolved)) {
                    throw new \LogicException("resolvedEntities.$key is required");
                }
            }

            $hasAnyEntity =
                !empty($resolved['brand_entity_id'])
                || !empty($resolved['condition_entity_id'])
                || !empty($resolved['color_entity_id']);

            if (!$hasAnyEntity) {
                throw new \DomainException('resolvedEntities must contain at least one entity_id.');
            }
        }

        try {
            DB::transaction(function () use ($analysisRequestId, $decidedUserId, $decidedByType, $input, $decisionType) {

                Log::info('[DecideUseCase] tx.begin', [
                    'analysisRequestId' => $analysisRequestId,
                    'decisionType'      => $decisionType,
                ]);

                // 0) request existence
                $req = $this->requests->findOrFail($analysisRequestId);
                Log::info('[DecideUseCase] request.ok', [
                    'analysisRequestId' => $analysisRequestId,
                    'request'           => method_exists($req, 'toArray') ? $req->toArray() : (string)get_class($req),
                ]);

                $resolved = $input['resolvedEntities'] ?? null;
                if (!is_array($resolved)) {
                    throw new \LogicException('resolvedEntities must be array');
                }

                // 1) review_decisions append (append-only)
                Log::info('[DecideUseCase] appendDecision.try', [
                    'analysisRequestId' => $analysisRequestId,
                    'resolved'          => $resolved,
                ]);

                $this->decisions->appendDecision([
                    'analysis_request_id' => $analysisRequestId,
                    'decision_type'       => $decisionType,
                    'resolved_entities'   => $resolved,
                    'after_snapshot'      => $input['after_snapshot'] ?? null,
                    'note'                => $input['note'] ?? null,
                    'decided_by_type'     => $decidedByType,
                    'decided_by'          => $decidedUserId,
                    'decided_at'          => now(),
                ]);

                Log::info('[DecideUseCase] appendDecision.ok');

                // 2) latest decision id（暫定）
                $latestDecision = $this->decisions->findLatestByAnalysisRequestId($analysisRequestId);
                $reviewDecisionId = $latestDecision?->id;

                Log::info('[DecideUseCase] latestDecision', [
                    'reviewDecisionId' => $reviewDecisionId,
                    'latestDecision'   => $latestDecision?->toArray(),
                ]);

                // 3) learning_candidates
                if ($decisionType === 'approve') {
                    Log::info('[DecideUseCase] approve.learningCandidates.begin');

                    foreach (['brand', 'condition', 'color'] as $entityType) {
                        $entityId = $resolved[$entityType . '_entity_id'] ?? null;
                        if ($entityId === null) {
                            continue;
                        }

                        // 入力語（beforeParsed）を proposed_name に保存
                        $inputValue = $input['beforeParsed'][$entityType] ?? null;

                        Log::info('[DecideUseCase] approve.learningCandidates.row', [
                            'entityType'  => $entityType,
                            'entityId'    => $entityId,
                            'inputValue'  => $inputValue,
                            'hasBeforeParsed' => isset($input['beforeParsed']),
                        ]);


// ★ proposed_name を必ず決定する（v3 FIXED）
$inputValue =
    $input['beforeParsed'][$entityType]
    ?? $input['afterParsed'][$entityType]
    ?? '(selected)';



// それでも無ければ最後の保険
if (!is_string($inputValue) || $inputValue === '') {
    $inputValue = '(selected)';
}
                        $row = [
                            'analysis_request_id' => $analysisRequestId,
                            'review_decision_id'  => $reviewDecisionId,
                            'entity_type'         => $entityType,
                            'proposed_name'       => $inputValue,
                            'normalized_key'      => $this->normalizeKey($inputValue),
                            'decision_type'       => 'approve',
                            'entity_id'           => (int)$entityId,
                            'source'              => 'human_review',
                            'confidence'          => null,
                            'status'              => 'pending',
                        ];

                        Log::info('[DecideUseCase] learningCandidates.append.try', $row);
                        $this->learningCandidates->append($row);
                        Log::info('[DecideUseCase] learningCandidates.append.ok', [
                            'entityType' => $entityType,
                        ]);
                    }
                }

                if ($decisionType === 'manual_override') {
                    Log::info('[DecideUseCase] manual_override.begin', [
                        'override' => $input['override'] ?? null,
                    ]);

                    foreach (['brand', 'condition', 'color'] as $entityType) {
                        $name = $input['override'][$entityType] ?? null;
                        if (!is_string($name) || $name === '') {
                            continue;
                        }

                        $newEntityId = $this->entityFactory->createCanonicalEntity(
                            $entityType,
                            $name,
                            $name,
                            'human'
                        );

                        $resolved[$entityType . '_entity_id'] = $newEntityId;

                        $row = [
                            'analysis_request_id' => $analysisRequestId,
                            'review_decision_id'  => $reviewDecisionId,
                            'entity_type'         => $entityType,
                            'proposed_name'       => $name,
                            'normalized_key'      => $this->normalizeKey($name),
                            'decision_type'       => 'manual_override',
                            'entity_id'           => (int)$newEntityId,
                            'source'              => 'human_review',
                            'confidence'          => 1.0,
                            'status'              => 'pending',
                        ];

                        Log::info('[DecideUseCase] learningCandidates.append.try', $row);
                        $this->learningCandidates->append($row);
                        Log::info('[DecideUseCase] learningCandidates.append.ok', [
                            'entityType' => $entityType,
                            'newEntityId' => $newEntityId,
                        ]);
                    }
                }

                // 4) apply confirmed
                if (in_array($decisionType, ['approve', 'edit_confirm', 'manual_override'], true)) {
                    Log::info('[DecideUseCase] applyConfirmed.try', [
                        'analysisRequestId' => $analysisRequestId,
                    ]);

                    $this->applyConfirmed->handle($analysisRequestId);

                    Log::info('[DecideUseCase] applyConfirmed.ok');
                }

                Log::info('[DecideUseCase] tx.end');
            }, 1);
        } catch (Throwable $e) {
            Log::error('[DecideUseCase] failed', [
                'analysisRequestId' => $analysisRequestId,
                'decisionType'      => $decisionType,
                'message'           => $e->getMessage(),
                'exception'         => get_class($e),
                'trace'             => substr($e->getTraceAsString(), 0, 1500),
            ]);
            throw $e;
        }
    }

    private function normalizeKey(string $name): string
    {
        $s = trim($name);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        return $s;
    }



}