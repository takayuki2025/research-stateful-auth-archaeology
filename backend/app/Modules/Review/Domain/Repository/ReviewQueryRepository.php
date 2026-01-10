<?php

namespace App\Modules\Review\Domain\Repository;

interface ReviewQueryRepository
{
    /**
     * v3: 最新の解析結果（提案）を返す。
     * ここでは item_entities / item_entity_tags の latest を「提案」として扱う。
     * 将来、analysis_results テーブルへ差し替え可能。
     */
    public function getLatestAnalysis(int $itemId): array;

    /** @return array<int, array> */
    public function listReviewItems(?string $status, ?float $confidenceMin, ?string $analyzedBy, int $limit = 50): array;

    public function getReviewItemDetail(int $itemId): array;
}