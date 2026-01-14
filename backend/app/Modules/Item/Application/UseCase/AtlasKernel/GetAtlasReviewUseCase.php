<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Application\Dto\AtlasKernel\AtlasReviewDto;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;

final class GetAtlasReviewUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
        private ReviewDecisionRepository $decisions,
    ) {}

    public function handle(
        string $shopCode,
        int $analysisRequestId,
    ): AtlasReviewDto {
        // ① Request（Bフェーズ主語）
        $request = $this->requests->findOrFail($analysisRequestId);

        // ② 最新 analysis_result（技術スナップショット）
        $result = $this->results->findByRequestId($analysisRequestId);
        if (! $result) {
            throw new \RuntimeException('analysis_result not found');
        }

        // ③ 最新 decision（あれば）
        $decision = $this->decisions->findLatestByRequestId($analysisRequestId);

        // AFTER（AI提案）
        $after = [
    'brand'     => $result->brandName,
    'condition' => $result->conditionName,
    'color'     => $result->colorName,
];

        // BEFORE（SoT or decision）
        $before = $decision && $decision->before_snapshot
            ? $decision->before_snapshot
            : $after;

        // edit_confirm の after_snapshot で上書き
        if ($decision && $decision->after_snapshot) {
            $after = array_merge($after, $decision->after_snapshot);
        }

        // diff 自動生成（v3固定）
        $diff = [];
        foreach ($after as $key => $afterValue) {
            $beforeValue = $before[$key] ?? null;
            if ($beforeValue !== $afterValue) {
                $diff[$key] = [
                    'before' => $beforeValue,
                    'after'  => $afterValue,
                ];
            }
        }

        // confidence / attributes
        // ✅ result->confidence_map が「配列」の可能性もあるので吸収
        $confidenceMap = $result->confidenceMap ?? [];
        if (is_string($confidenceMap)) {
            $confidenceMap = json_decode($confidenceMap, true) ?: [];
        }
        if (!is_array($confidenceMap)) {
            $confidenceMap = [];
        }

        $attributes = [];
foreach ($after as $key => $value) {
    $attributes[$key] = [
        'value'      => $value,
        'confidence' => $confidenceMap[$key] ?? null,
        'evidence'   => null,
    ];
}

        return new AtlasReviewDto(
    requestId: $request->id,
    status: $request->status,
    overallConfidence: $result->overallConfidence,
    before: $before,
    after: $after,
    diff: $diff,
    attributes: $attributes,
);
    }
}
