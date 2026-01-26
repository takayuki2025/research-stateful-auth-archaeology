<?php

namespace App\Modules\ProviderIntel\Domain\Repository;

interface DocumentDiffRepository
{
    public function save(?int $projectId, ?int $beforeId, int $afterId, ?array $summary): int;
    public function find(int $id): ?array;
}