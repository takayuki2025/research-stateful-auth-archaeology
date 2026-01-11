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
    AnalysisResultRepository $analysisRepo,
    ApplyProvisionalAnalysisUseCase $applyProvisional
): void {
    $result = $atlasKernel->requestAnalysis(
        itemId: $this->itemId,
        rawText: $this->rawText,
        tenantId: $this->tenantId,
    );

    // analysis_results 保存
    $analysisRepo->save($this->itemId, [
        'analysis' => $result->toArray(),
        'status'   => 'provisional',
    ]);

    // ★ 仮 Entity 自動生成
    $applyProvisional->handle(
        $this->itemId,
        $result->toArray()
    );
}
}