<?php

namespace App\Modules\Item\Application\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Application\UseCase\AtlasKernel\ApplyProvisionalAnalysisUseCase;

final class AnalyzeItemForReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $itemId,
        private string $rawText,
        private ?int $tenantId,
        private string $source,
    ) {}

    public function handle(
        AtlasKernelService $atlasKernel,
        AnalysisResultRepository $analysisRepo,
        ApplyProvisionalAnalysisUseCase $applyUseCase
    ): void {
        // ① 解析
        $analysisResult = $atlasKernel->requestAnalysis(
            itemId: $this->itemId,
            rawText: $this->rawText,
            tenantId: $this->tenantId,
        );

        $payload = [
            'source'   => $this->source,
            'input'    => ['raw_text' => $this->rawText],
            'analysis' => $analysisResult->toArray(),
        ];

        // ② analysis_results 保存
        $analysisRepo->save(
            itemId: $this->itemId,
            payload: $payload
        );

        // ③ ★AI一次結果を仮 entity として反映
        $applyUseCase->handle(
            $this->itemId,
            $payload['analysis']
        );
    }
}