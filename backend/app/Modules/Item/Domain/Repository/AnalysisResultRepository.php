<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\AnalysisResult;

interface AnalysisResultRepository
{
    public function saveByRequestId(int $requestId, array $payload): void;
    public function supersedeByItem(int $itemId): void;
    public function findByRequestId(int $analysisRequestId): ?AnalysisResult;
}