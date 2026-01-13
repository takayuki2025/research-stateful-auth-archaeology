<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;

final class ReplayAtlasRequestUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
    ) {}

    public function handle(int $analysisRequestId, string $version, ?string $reason): int
    {
        return DB::transaction(function () use ($analysisRequestId, $version, $reason) {

            $old = $this->requests->findOrFail($analysisRequestId);

            // ✅ v3固定：新規 request を作る
            $newId = $this->requests->create([
                'item_id'          => $old->itemId(),
                'status'           => 'pending',
                'analysis_version' => $version,
                'reason'           => $reason,
            ]);

            // ✅ 非同期で再解析（request_id を Job に渡す）
            AnalyzeItemForReviewJob::dispatch(
                itemId: $old->itemId(),
                rawText: $old->rawText(),
                tenantId: $old->tenantId(),
                source: 'replay',
                analysisRequestId: $newId,
            );

            return $newId;
        });
    }
}
