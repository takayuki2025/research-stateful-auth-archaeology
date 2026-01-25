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
use Illuminate\Support\Facades\DB;

final class AnalyzeItemForReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $analysisRequestId,
        private string $source = 'initial',
    ) {}

    public function handle(
        AtlasKernelService $atlasKernel,
        AnalysisRequestRepository $requestRepo,
        AnalysisResultRepository $resultRepo,
        ApplyProvisionalAnalysisUseCase $applyUseCase,
    ): void {
        // ① Request を取得（SoT）
        $request = $requestRepo->findOrFail($this->analysisRequestId);

        // ② 冪等ガード
        if ($request->isDone()) {
            return;
        }

        try {
            // ③ 入力情報の取得（requestId主語）
            $itemId   = $request->itemId();
            $tenantId = $request->tenantId();
            $rawText  = $request->rawText();

            // ✅ draft.brand（attributes入力）を brand_text として優先注入
            $brandText = null;
            $draftId = $request->itemDraftId(); // nullable想定

            if ($draftId) {
                $brandText = DB::table('item_drafts')
                    ->where('id', $draftId)
                    ->value('brand');
            }

            // ④ AtlasKernel への解析依頼（context付き）
            $analysis = $atlasKernel->requestAnalysis(
                $itemId,
                $rawText,
                $tenantId,
                [
                    'brand_text' => $brandText, // ★ここが効く
                ]
            );

            // ⑤ UX fallback 用 Item（安全参照）
            $item = Item::find($itemId);

            // ⑥ persistPayload 作成（元のロジック維持）
            $persistPayload = [
                'analysis_request_id' => $request->id(),
                'item_id'             => $itemId,

                'brand_name'          => data_get($analysis, 'brand.name') ?? $item?->brand,
                'condition_name'      => data_get($analysis, 'condition.name'),
                'color_name'          => data_get($analysis, 'color.name'),

                'classified_tokens'   => [
                    'brand'     => data_get($analysis, 'tokens.brand', []),
                    'condition' => data_get($analysis, 'tokens.condition', []),
                    'color'     => data_get($analysis, 'tokens.color', []),
                ],

                'confidence_map'      => $analysis['confidence_map'] ?? [
                    'brand'     => 0.0,
                    'condition' => 0.0,
                    'color'     => 0.0,
                ],

                'overall_confidence'  => $analysis['overall_confidence'] ?? 0.0,
                'source'              => 'ai_provisional',
                'status'              => 'active',
            ];

            // ⑦ 保存
            $resultRepo->saveByRequestId($request->id(), $persistPayload);

            // ⑧ 正常終了マーク
            $requestRepo->markDone($request->id());

        } catch (\Throwable $e) {
            $requestRepo->markFailed(
                $request->id(),
                'ATLAS_ANALYZE_FAILED',
                $e->getMessage()
            );
            throw $e;
        }
    }
}