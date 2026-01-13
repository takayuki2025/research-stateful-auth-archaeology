<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Query;

interface AtlasReviewQuery
{
    /**
     * Review 表示に必要な “生データ” を返す（UseCase が整形する）
     */
    public function fetchReviewSource(string $shopCode, int $analysisRequestId): array;
}