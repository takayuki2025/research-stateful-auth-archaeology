<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Application\UseCase\AtlasKernel\ApplyProvisionalAnalysisUseCase;
use App\Models\Item;

final class AnalyzeItemForReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * v3 固定：
     * Job は analysisRequestId だけを主語にする
     */
    public function __construct(
        private int $analysisRequestId,
        private string $source = 'initial', // initial | replay | system
    ) {}

    public function handle(
        AtlasKernelService $atlasKernel,
        AnalysisRequestRepository $requestRepo,
        AnalysisResultRepository $resultRepo,
        ApplyProvisionalAnalysisUseCase $applyUseCase,
    ): void {

        /**
         * ① Request を SoT として取得（唯一の主語）
         */
        $request = $requestRepo->findOrFail($this->analysisRequestId);

        /**
         * ② 冪等ガード（すでに done なら何もしない）
         */
        if ($request->isDone()) {
            return;
        }

        /**
         * ③ 入力は request からのみ取得
         */
        $itemId   = $request->itemId();
        $tenantId = $request->tenantId();
        $rawText  = $request->rawText();

        /**
         * ④ AtlasKernel へ解析依頼
         * ※ v3 方針：named argument を使わない
         */
        $analysis = $atlasKernel->requestAnalysis(
            $itemId,
            $rawText,
            $tenantId,
        );

        /**
         * ⑤ UX fallback 用 Item（安全参照）
         */
        $item = Item::find($itemId);

        /**
         * ⑥ analysis_results 保存 payload（requestId 主語）
         */
        $persistPayload = [
            'analysis_request_id' => $request->id(),

            // 参照用途（SoT ではない）
            'item_id' => $itemId,

            'brand_name' => data_get($analysis, 'brand.name')
                ?? $item?->brand,

            'condition_name' => data_get($analysis, 'condition.name'),
            'color_name'     => data_get($analysis, 'color.name'),

            'classified_tokens' => [
                'brand'     => data_get($analysis, 'tokens.brand', []),
                'condition' => data_get($analysis, 'tokens.condition', []),
                'color'     => data_get($analysis, 'tokens.color', []),
            ],

            'confidence_map' => $analysis['confidence_map'] ?? [
                'brand'     => 0.0,
                'condition' => 0.0,
                'color'     => 0.0,
            ],

            'overall_confidence' => $analysis['overall_confidence'] ?? 0.0,

            'source' => 'ai_provisional',
            'status' => 'active',
        ];

        /**
         * ⑦ 保存（requestId 主語）
         */
        $resultRepo->saveByRequestId(
            $request->id(),
            $persistPayload,
        );

        /**
         * ⑧ request を done にする
         */
        $requestRepo->markDone($request->id());

        // v3 Aフェーズでは SoT 反映しない
        // $applyUseCase->handle($request->id(), $analysis);
    }
}