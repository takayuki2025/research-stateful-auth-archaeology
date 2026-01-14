<?php

namespace App\Modules\Item\Application\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Application\UseCase\AtlasKernel\ApplyProvisionalAnalysisUseCase;
use App\Models\Item;

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
        AnalysisRequestRepository $requestRepo,
        AnalysisResultRepository $analysisRepo,
        ApplyProvisionalAnalysisUseCase $applyUseCase
    ): void {

        $record = $requestRepo->reserveOrGet(
            tenantId: $this->tenantId,
            itemId: $this->itemId,
            analysisVersion: 'v3',
            payloadHash: sha1($this->rawText),
            idempotencyKey: sha1($this->itemId . ':' . $this->rawText)
        );

        $analysisResult = $atlasKernel->requestAnalysis(
            itemId: $this->itemId,
            rawText: $this->rawText,
            tenantId: $this->tenantId,
        );

        $analysis = $analysisResult->toProvisionalDisplay();

        // ★ UX 優先 fallback 用
        $item = Item::find($this->itemId);

        $persistPayload = [
            // ここは「payloadの一部」でも良いが、保存主語は requestId に固定する
            'analysis_request_id' => $record->id,

            'item_id' => $this->itemId, // DBカラムがあるなら保持（参照用途）
            'brand_name' => data_get($analysis, 'brand.name') ?? $item?->brand,
            'condition_name' => data_get($analysis, 'condition.name'),
            'color_name'     => data_get($analysis, 'color.name'),

            // ★ confidence_map は GetAtlasReviewUseCase が key=brand/color/condition を期待
            'confidence_map' => $analysis['confidence_map'] ?? [
                'brand' => 0.0,
                'color' => 0.0,
                'condition' => 0.0,
            ],
            'overall_confidence' => $analysis['overall_confidence'] ?? 0.0,

            'source' => 'ai_provisional',
            'status' => 'active',
        ];

        // ✅ requestId 主語で保存（itemIdを引数として渡さない）
        $analysisRepo->saveByRequestId(
            requestId: $record->id,
            payload: $persistPayload
        );

        $requestRepo->markDone($record->id);

        // NOTE: Aフェーズなら apply はしない選択もあるが、今は既存通り維持
        $applyUseCase->handle(
            $this->itemId,
            $analysis
        );
    }
}