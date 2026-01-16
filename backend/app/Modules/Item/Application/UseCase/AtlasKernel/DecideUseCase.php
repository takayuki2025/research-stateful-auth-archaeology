<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;

final class DecideUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private ReviewDecisionRepository $decisions,
        private ApplyConfirmedDecisionUseCase $applyConfirmed,
    ) {}

    /**
     * v3固定：Controller から validated 配列をそのまま受ける
     */
    public function handle(
    int $analysisRequestId,
    int $decidedUserId,
    string $decidedByType,
    array $input,
): void {
    $decisionType = $input['decision_type'];

    if (in_array($decisionType, ['approve', 'edit_confirm', 'manual_override'], true)) {
        $resolved = $input['resolvedEntities'] ?? null;

if (!is_array($resolved)) {
    throw new \LogicException('resolved_entities must be array');
}

$hasAnyEntity =
    !empty($resolved['brand_entity_id'])
    || !empty($resolved['condition_entity_id'])
    || !empty($resolved['color_entity_id']);

if (!$hasAnyEntity) {
    throw new \DomainException(
        'resolved_entities must contain at least one entity_id.'
    );
}
    }

    DB::transaction(function () use (
        $analysisRequestId,
        $decidedUserId,
        $decidedByType,
        $input,
        $decisionType
    ) {
        // request existence
        $this->requests->findOrFail($analysisRequestId);

        $resolved = $input['resolvedEntities'] ?? null;

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


        // 採用系は即 Apply
        if (in_array($decisionType, ['approve', 'edit_confirm', 'manual_override'], true)) {
            $this->applyConfirmed->handle($analysisRequestId);
        }
    });
}
}