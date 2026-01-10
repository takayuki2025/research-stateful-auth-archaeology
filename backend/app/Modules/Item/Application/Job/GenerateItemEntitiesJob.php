<?php

namespace App\Modules\Item\Application\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

final class GenerateItemEntitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $itemId,
        private string $rawText,
        private ?int $tenantId
    ) {}

    public function handle(
        AtlasKernelService $atlasKernel,
        AnalysisResultRepository $analysisRepo
    ): void {
        $result = $atlasKernel->requestAnalysis(
            itemId: $this->itemId,
            rawText: $this->rawText,
            tenantId: $this->tenantId,
        );

        // ★ DB保存は analysis_results のみ
        $analysisRepo->save($this->itemId, $result);
    }
}