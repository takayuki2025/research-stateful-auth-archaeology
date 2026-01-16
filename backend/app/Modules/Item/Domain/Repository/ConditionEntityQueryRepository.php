<?php

namespace App\Modules\Item\Domain\Repository;

interface ConditionEntityQueryRepository
{
     public function resolveCanonicalByEntityId(int $entityId): int;

    public function resolveCanonicalByName(string $input): ?int;
}