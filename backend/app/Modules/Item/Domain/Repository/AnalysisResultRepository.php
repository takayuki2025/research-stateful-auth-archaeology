<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

interface AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void;

    public function supersedeByItem(int $itemId): void;

    public function findByRequestId(int $requestId): ?AnalysisResult;
}