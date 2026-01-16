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



$decision = $this->decisions
    ->findLatestByAnalysisRequestId($analysisRequestId);

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
 * UI補助用：BEFORE を分解（v3補助・SoT非侵食）
 */
$beforeParsed = $this->parseBeforeText($learning, $tokens);


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
            beforeParsed: $beforeParsed,
        );
    }

/**
 * UI補助用（v3固定・確定）
 * - BEFORE は必ず rawText 由来
 * - tokens は「切り分けのヒント」にのみ使用（正規化しない）
 * - 日本語連結（例: あっぷる美品あお）を安定分解
 */
private function parseBeforeText(?string $rawText, array $tokens): array
{
    if (!$rawText) {
        return [];
    }

    $rawText = trim($rawText);

    // 日本語安全：先頭2語 + 残り
    $parts = preg_split('/\s+/u', $rawText, 3);

    $name        = $parts[0] ?? null;
    $description = $parts[1] ?? null;
    $tail        = trim($parts[2] ?? '');

    $brand = null;
    $condition = null;
    $color = null;

    if ($tail === '') {
        return [
            'name'         => $this->nullIfEmpty($name),
            'description'  => $this->nullIfEmpty($description),
            'brand'        => null,
            'condition'    => null,
            'color'        => null,
            'raw'          => $rawText,
            'derived_from' => 'rawText(v3_tail_empty)',
        ];
    }

    /**
     * ① color（tokens ヒントで rawText 末尾から切り出す）
     *    ※ 比較時のみ かな正規化
     */
    if (!empty($tokens['color']) && is_array($tokens['color'])) {
        foreach ($tokens['color'] as $hint) {
            if (!is_string($hint)) continue;

            $normTail = $this->normalizeKana($tail);
            $normHint = $this->normalizeKana($hint);

            if (mb_substr($normTail, -mb_strlen($normHint)) === $normHint) {
                // raw 側の実文字を切り出す
                $color = mb_substr($tail, -mb_strlen($normHint));
                $tail  = mb_substr($tail, 0, mb_strlen($tail) - mb_strlen($normHint));
                break;
            }
        }
    }

    $tail = trim($tail);

    /**
     * ② condition（同様に後ろから）
     */
    if (!empty($tokens['condition']) && is_array($tokens['condition'])) {
        foreach ($tokens['condition'] as $hint) {
            if (!is_string($hint)) continue;

            $normTail = $this->normalizeKana($tail);
            $normHint = $this->normalizeKana($hint);

            if (mb_substr($normTail, -mb_strlen($normHint)) === $normHint) {
                $condition = mb_substr($tail, -mb_strlen($normHint));
                $tail      = mb_substr($tail, 0, mb_strlen($tail) - mb_strlen($normHint));
                break;
            }
        }
    }

    $tail = trim($tail);

    /**
     * ③ 残りすべて brand（raw 痕跡を最優先）
     */
    $brand = $this->nullIfEmpty($tail);

    return [
        'name'         => $this->nullIfEmpty($name),
        'description'  => $this->nullIfEmpty($description),
        'brand'        => $brand,
        'condition'    => $this->nullIfEmpty($condition),
        'color'        => $this->nullIfEmpty($color),
        'raw'          => $rawText,
        'derived_from' => 'rawText(v3_tokens_suffix_split_kana_safe)',
    ];
}

private function nullIfEmpty(?string $v): ?string
{
    if ($v === null) return null;
    $v = trim($v);
    return $v === '' ? null : $v;
}

/**
 * 比較専用：カタカナ → ひらがな
 * ※ 値の保存には絶対に使わない
 */
private function normalizeKana(string $s): string
{
    return mb_convert_kana($s, 'c', 'UTF-8');
}

}