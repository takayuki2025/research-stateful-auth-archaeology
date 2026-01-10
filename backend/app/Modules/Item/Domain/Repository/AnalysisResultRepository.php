<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Dto\AtlasAnalysisResult;

interface AnalysisResultRepository
{
    public function save(int $itemId, AtlasAnalysisResult $result): void;
    public function markRejected(int $itemId): void;
}