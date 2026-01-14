<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;

final class ReplayAtlasRequestUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
    ) {}

    public function handle(
        int $oldRequestId,
        string $analysisVersion,
        ?string $reason,
    ): int {
        return DB::transaction(function () use ($oldRequestId, $analysisVersion, $reason) {

            $old = $this->requests->findOrFail($oldRequestId);

            $newRequestId = $this->requests->create([
                'tenant_id'        => $old->tenantId(),
                'item_id'          => $old->itemId(),
                // 'item_draft_id'    => $old->itemDraftId(),
                'raw_text'         => $old->rawText(),
                'analysis_version' => $analysisVersion,
            ]);

            AnalyzeItemForReviewJob::dispatch(
                analysisRequestId: $newRequestId
            );

            return $newRequestId;
        });
    }
}