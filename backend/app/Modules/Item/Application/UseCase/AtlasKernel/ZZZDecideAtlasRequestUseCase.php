<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Item\Domain\Entity\LearningCandidate;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Service\EntityFactory;

final class DecideAtlasRequestUseCase
{
    public function __construct(
        private ReviewDecisionRepository $decisions,
        private EntityFactory $entityFactory,
    ) {}

    public function handle(
        int $analysisRequestId,
        string $decisionType,
        array $resolvedEntities,
        array $rawInputs
    ): void {
        DB::transaction(function () use (
            $analysisRequestId,
            $decisionType,
            $resolvedEntities,
            $rawInputs
        ) {
            /** 1️⃣ review_decision 作成 */
            $decision = $this->decisions->create([
                'analysis_request_id' => $analysisRequestId,
                'decision_type'       => $decisionType,
                'resolved_entities'   => $resolvedEntities,
            ]);

            /** 2️⃣ learning_candidates（全 decision 共通） */
            foreach (['brand', 'condition', 'color'] as $type) {
                if (!empty($rawInputs[$type])) {
                    LearningCandidate::create([
                        'entity_type'         => $type,
                        'proposed_name'       => $rawInputs[$type],
                        'normalized_key'      => Str::lower($rawInputs[$type]),
                        'source'              => 'human',
                        'confidence'          => null,
                        'analysis_request_id' => $analysisRequestId,
                        'review_decision_id'  => $decision->id,
                        'status'              => $decisionType === 'reject'
                            ? 'rejected'
                            : 'pending',
                    ]);
                }
            }

            /** 3️⃣ Manual Override のみ canonical 生成 */
            if ($decisionType === 'manual_override') {
                foreach ($resolvedEntities as $key => $value) {
                    if (str_ends_with($key, '_canonical')) {
                        $type = str_replace('_canonical', '', $key);

                        $entityId = $this->entityFactory
                            ->createCanonicalEntity(
                                $type,
                                $value,
                                $value,
                                'human'
                            );

                        $resolvedEntities[$type . '_entity_id'] = $entityId;
                    }
                }

                $decision->update([
                    'resolved_entities' => $resolvedEntities,
                ]);
            }
        });
    }
}