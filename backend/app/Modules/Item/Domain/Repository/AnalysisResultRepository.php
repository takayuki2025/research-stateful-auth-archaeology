<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\AnalysisResult;

interface AnalysisResultRepository
{
    public function saveByRequestId(int $requestId, array $payload): void;

    // 互換が不要なら削除してOK（ただし全呼び出し側を修正済みにすること）
    public function save(int $itemId, array $payload): void;

    public function supersedeByItem(int $itemId): void;

    public function findByRequestId(int $analysisRequestId): ?AnalysisResult;
}