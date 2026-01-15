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
        private ItemDraftRepository $drafts, // ✅ BEFORE（SoT）
    ) {}

    public function handle(string $shopCode, int $analysisRequestId): AtlasReviewDto
    {
        /**
         * ① Analysis Request
         */
        $request = $this->requests->findOrFail($analysisRequestId);

        /**
         * ② Analysis Result（AI提案・AFTER）
         */
        $result = $this->results->findByRequestId($analysisRequestId);
        if (! $result) {
            throw new \RuntimeException('analysis_result not found');
        }

        /**
         * ③ Review Decision（あれば AFTER を上書き）
         */
        $decision = $this->decisions->findLatestByRequestId($analysisRequestId);

        /**
         * learning（人間入力そのもの）
         */
        $learning = $request->rawText();

        /**
         * tokens（AI抽出の副産物・UI補助用）
         */
        $tokens = [
            'brand'     => [],
            'condition' => [],
            'color'     => [],
        ];

        if (is_array($result->classifiedTokens)) {
            $tokens = array_merge($tokens, $result->classifiedTokens);
        }

        /**
         * ✅ v3固定：BEFORE = item_drafts（人間SoT）
         */
        $before = [
            'brand'     => null,
            'condition' => null,
            'color'     => null,
        ];

        $draftId = $request->itemDraftId();
        if (is_string($draftId)) {
            $draft = $this->drafts->findById($draftId);
            if ($draft !== null) {
                $before = [
                    'brand'     => $draft->brandRaw(),
                    'condition' => $draft->conditionRaw(),
                    'color'     => $draft->colorRaw(),
                ];
            }
        }

        /**
         * ✅ v3固定：AFTER = analysis_results（AI提案）
         */
        $after = [
            'brand'     => $result->brandName,
            'condition' => $result->conditionName,
            'color'     => $result->colorName,
        ];

        /**
         * 人間の edit_confirm があれば AFTER を上書き
         */
        if ($decision && is_array($decision->after_snapshot) && ! empty($decision->after_snapshot)) {
            foreach (['brand', 'condition', 'color'] as $k) {
                if (array_key_exists($k, $decision->after_snapshot)) {
                    $after[$k] = $decision->after_snapshot[$k];
                }
            }
        }

        /**
         * diff 自動生成（BEFORE vs AFTER）
         */
        $diff = [];
        foreach (['brand', 'condition', 'color'] as $key) {
            $b = $before[$key];
            $a = $after[$key];

            $bs = $b === null ? '' : (string) $b;
            $as = $a === null ? '' : (string) $a;

            if ($bs !== $as) {
                $diff[$key] = [
                    'before' => $b,
                    'after'  => $a,
                ];
            }
        }

        /**
         * confidence_map（AFTER 側）
         */
        $confidenceMap = $result->confidenceMap ?? [];
        if (is_string($confidenceMap)) {
            $confidenceMap = json_decode($confidenceMap, true) ?: [];
        }
        if (! is_array($confidenceMap)) {
            $confidenceMap = [];
        }

        /**
         * attributes（UI表示用）
         */
        $attributes = [];
        foreach (['brand', 'condition', 'color'] as $key) {
            $attributes[$key] = [
                'value'      => $after[$key],
                'confidence' => $confidenceMap[$key] ?? null,
                'evidence'   => null,
            ];
        }

        /**
         * DTO
         */
        return new AtlasReviewDto(
            requestId: $request->id(),
            status: $request->status(),
            learning: $learning,
            tokens: $tokens,
            overallConfidence: $result->overallConfidence,
            before: $before,
            after: $after,
            diff: $diff,
            confidenceMap: $confidenceMap,
            attributes: $attributes,
        );
    }
}