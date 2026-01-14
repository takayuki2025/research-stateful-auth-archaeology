<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;
use Illuminate\Support\Facades\DB;

final class ReplayAnalysisUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
    ) {}

    public function handle(
        int $analysisRequestId,
        string $reason,
    ): int {
        return DB::transaction(function () use ($analysisRequestId, $reason) {

            $old = $this->requests->findOrFail($analysisRequestId);

            // v3 固定：request を複製
            $newRequestId = $this->requests->create([
                'item_id'          => $old->itemId(),
                'raw_text'         => $old->rawText(),
                'analysis_version' => $old->analysisVersion(),
                'status'           => 'pending',
                'replay_of'        => $old->id(),
                'replay_reason'    => $reason,
            ]);

            // v3 固定：Job 再投入
            AnalyzeItemForReviewJob::dispatch($newRequestId);

            return $newRequestId;
        });
    }
}