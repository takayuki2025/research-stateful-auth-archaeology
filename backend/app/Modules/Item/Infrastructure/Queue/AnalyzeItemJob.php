<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Queue;

use App\Modules\Atlas\Application\Service\AtlasKernelAnalyzer;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class AnalyzeItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $analysisRequestId
    ) {}

    public function handle(
    AnalysisRequestRepository $requests,
    AtlasKernelAnalyzer $analyzer,
): void {
    if (! $requests->markRunning($this->analysisRequestId)) {
        return;
    }

    try {
        $result = $analyzer->analyze($this->analysisRequestId);

        // TODO（次チャット）:
        // - item_entities 反映
        // - review_decisions system_approve / edit_confirm 生成

        $requests->markDone($this->analysisRequestId);
    } catch (\Throwable $e) {
        $requests->markFailed(
            $this->analysisRequestId,
            'ANALYZE_FAILED',
            $e->getMessage()
        );
        throw $e;
    }
}