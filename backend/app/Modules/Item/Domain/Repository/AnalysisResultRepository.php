<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\AnalysisResult;

interface AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void;

    public function supersedeByItem(int $itemId): void;

    public function findByRequestId(int $requestId): ?AnalysisResult;
}