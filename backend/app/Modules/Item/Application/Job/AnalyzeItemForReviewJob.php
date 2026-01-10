<?php

namespace App\Modules\Item\Application\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

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
        AnalysisResultRepository $analysisRepo
    ): void {
        // Domain に完全委譲
        $analysisResult = $atlasKernel->requestAnalysis(
            itemId: $this->itemId,
            rawText: $this->rawText,
            tenantId: $this->tenantId,
        );

        // v3 payload をここで「包むだけ」
        $analysisRepo->save(
            itemId: $this->itemId,
            payload: [
                'source' => $this->source,
                'input' => [
                    'raw_text' => $this->rawText,
                ],
                'analysis' => $analysisResult->toArray(), // ★唯一の正解
            ]
        );
    }
}