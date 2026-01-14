<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Application\Dto\AtlasKernel\AtlasReviewDto;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\ItemDraftRepository;

final class GetAtlasReviewUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
        private ReviewDecisionRepository $decisions,
        private ItemDraftRepository $drafts, // ✅ v3固定：BEFORE(SoT)取得
    ) {}

    public function handle(string $shopCode, int $analysisRequestId): AtlasReviewDto
    {
        // ① request
        $request = $this->requests->findOrFail($analysisRequestId);

        // ② AFTER: analysis_results（AI提案）
        $result = $this->results->findByRequestId($analysisRequestId);
        if (! $result) {
            throw new \RuntimeException('analysis_result not found');
        }

        // ③ decision（存在しても BEFORE の主語にはしない）
        $decision = $this->decisions->findLatestByRequestId($analysisRequestId);



        // ✅ v3固定（安全版）：BEFORE は item_drafts が「あれば使う」
$draftId = $request->itemDraftId();

$before = [
    'brand'     => null,
    'condition' => null,
    'color'     => null,
];

if ($draftId !== null) {
    $draft = $this->drafts->findById($draftId);

    if ($draft) {
        $before = [
            'brand'     => $draft->brandRaw(),
            'condition' => $draft->conditionRaw(),
            'color'     => $draft->colorRaw(),
        ];
    }
}

        // ✅ v3固定：AFTER は analysis_result そのもの（AI提案）
        $after = [
            'brand'     => $result->brandName,
            'condition' => $result->conditionName,
            'color'     => $result->colorName,
        ];

        // ✅ v3固定：edit_confirm の “after_snapshot” がある場合のみ AFTER を上書き（人間の編集確定）
        if ($decision && is_array($decision->after_snapshot) && ! empty($decision->after_snapshot)) {
            foreach (['brand', 'condition', 'color'] as $k) {
                if (array_key_exists($k, $decision->after_snapshot)) {
                    $after[$k] = $decision->after_snapshot[$k];
                }
            }
        }

        // ✅ v3固定：diff 自動生成（brand/condition/color のみ）
        $diff = [];
        foreach (['brand', 'condition', 'color'] as $key) {
            $b = $before[$key] ?? null;
            $a = $after[$key] ?? null;

            // 厳密比較は UI 事故を生みやすいので string 正規化して比較
            $bs = $b === null ? '' : (string) $b;
            $as = $a === null ? '' : (string) $a;

            if ($bs !== $as) {
                $diff[$key] = [
                    'before' => $b,
                    'after'  => $a,
                ];
            }
        }

        // ✅ confidence_map（AFTER側のみ参照）
        $confidenceMap = $result->confidenceMap ?? [];
        if (is_string($confidenceMap)) {
            $confidenceMap = json_decode($confidenceMap, true) ?: [];
        }
        if (! is_array($confidenceMap)) {
            $confidenceMap = [];
        }

        // attributes（UI表示用：AFTER + confidence）
        $attributes = [];
        foreach (['brand', 'condition', 'color'] as $key) {
            $attributes[$key] = [
                'value'      => $after[$key] ?? null,
                'confidence' => $confidenceMap[$key] ?? null,
                'evidence'   => null,
            ];
        }

        return new AtlasReviewDto(
            requestId: $request->id(),
            status: $request->status(),
            overallConfidence: $result->overallConfidence,
            before: $before,
            after: $after,
            diff: $diff,
            confidenceMap: $confidenceMap,
            attributes: $attributes,
        );
    }
}
