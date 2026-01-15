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
        $decision = $this->decisions->findLatestByAnalysisRequestId($analysisRequestId);

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

        // classifiedTokens の実体が JSON string の可能性もあるのでガード
        $classifiedTokens = $result->classifiedTokens ?? null;
        if (is_string($classifiedTokens)) {
            $decoded = json_decode($classifiedTokens, true);
            $classifiedTokens = is_array($decoded) ? $decoded : null;
        }
        if (is_array($classifiedTokens)) {
            // 想定キーのみマージ（余計なキーが混ざっても安全）
            foreach (['brand', 'condition', 'color'] as $k) {
                if (isset($classifiedTokens[$k]) && is_array($classifiedTokens[$k])) {
                    $tokens[$k] = $classifiedTokens[$k];
                }
            }
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
        if (is_string($draftId) && $draftId !== '') {
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
         * ✅ v3固定：AFTER（value主語）
         * - afterValue: UI/差分比較の主語（string|null）
         * - afterMeta : human_confirmed 等の付随情報（confidence/source等）
         */
        $afterValue = [
            'brand'     => $result->brandName ?? null,
            'condition' => $result->conditionName ?? null,
            'color'     => $result->colorName ?? null,
        ];

        $afterMeta = [
            'brand'     => null,
            'condition' => null,
            'color'     => null,
        ];

        /**
         * 人間の edit_confirm があれば AFTER を上書き（v3: {value, confidence, ...}）
         */
        if ($decision && is_array($decision->after_snapshot) && ! empty($decision->after_snapshot)) {
            foreach (['brand', 'condition', 'color'] as $k) {
                $node = $decision->after_snapshot[$k] ?? null;

                // v3形式: ['value' => 'Apple', 'confidence' => 0.9, ...]
                if (is_array($node) && array_key_exists('value', $node)) {
                    $afterValue[$k] = $node['value'];
                    $afterMeta[$k]  = $node;
                    continue;
                }

                // 互換: 古い形式で 'Apple' のように直接入ってくる場合
                if (is_string($node) || is_null($node)) {
                    $afterValue[$k] = $node;
                    $afterMeta[$k]  = null;
                }
            }
        }

        /**
         * diff 自動生成（BEFORE value vs AFTER value）
         */
        $diff = [];
        foreach (['brand', 'condition', 'color'] as $key) {
            $b = $before[$key];
            $a = $afterValue[$key];

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
         * confidence_map（AI側）
         */
        $confidenceMap = $result->confidenceMap ?? [];
        if (is_string($confidenceMap)) {
            $decoded = json_decode($confidenceMap, true);
            $confidenceMap = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($confidenceMap)) {
            $confidenceMap = [];
        }

        /**
         * attributes（UI表示用）
         * - value は afterValue（必ず string|null）
         * - confidence は human(afterMeta) があれば優先。なければ AI(confidenceMap)
         */
        $attributes = [];
        foreach (['brand', 'condition', 'color'] as $key) {
            $humanConfidence = null;
            $humanSource = null;

            if (is_array($afterMeta[$key])) {
                $humanConfidence = $afterMeta[$key]['confidence'] ?? null;
                $humanSource     = $afterMeta[$key]['source'] ?? null;
            }

            $attributes[$key] = [
                'value'      => $afterValue[$key],
                'confidence' => $humanConfidence ?? ($confidenceMap[$key] ?? null),
                'source'     => $humanSource ?? 'ai',
                'evidence'   => null,
            ];
        }

        /**
         * DTO
         * ✅ after は value 主語（string|null）で返す（ここが v3 固定）
         */
        return new AtlasReviewDto(
            requestId: $request->id(),
            status: $request->status(),
            learning: $learning,
            tokens: $tokens,
            overallConfidence: $result->overallConfidence,
            before: $before,
            after: $afterValue,
            diff: $diff,
            confidenceMap: $confidenceMap,
            attributes: $attributes,
        );
    }
}
