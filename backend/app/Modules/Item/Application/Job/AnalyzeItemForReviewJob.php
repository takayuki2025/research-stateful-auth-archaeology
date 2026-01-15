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
     * Job は requestId だけを主語にする
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
         * ① Request を SoT として取得
         */
        $request = $requestRepo->findOrFail($this->analysisRequestId);

        /**
         * ② 冪等ガード（すでに done なら何もしない）
         */
        if ($request->isDone()) {
            return;
        }

        /**
         * ③ 入力データは request からのみ取得
         */
        $itemId   = $request->itemId();
        $tenantId = $request->tenantId();
        $rawText  = $request->rawText();   // item_draft / SoT snapshot

        /**
         * ④ AtlasKernel へ解析依頼
         * ※ v3 方針：Domain Service では named argument を使わない
         */
        $analysisResult = $atlasKernel->requestAnalysis(
            $itemId,
            $rawText,
            $tenantId,
        );

        /**
         * ⑤ 表示用（暫定）構造へ変換
         */
        $analysis = $analysisResult;

        /**
         * ⑥ UX fallback 用の Item 参照（安全）
         */
        $item = Item::find($itemId);

        /**
         * ⑦ analysis_results 保存 payload（requestId 主語）
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
         * ⑧ 保存（requestId 主語）
         */
        $resultRepo->saveByRequestId(
            $request->id(),
            $persistPayload,
        );

        /**
         * ⑨ request を done にする
         */
        $requestRepo->markDone($request->id());

        /**
         * ⑩ Aフェーズ：即時反映（将来は PolicyEngine に委譲）
         * ※ ApplyUseCase も requestId 主語
         */
        $applyUseCase->handle(
            $request->id(),
            $analysis,
        );
    }
}