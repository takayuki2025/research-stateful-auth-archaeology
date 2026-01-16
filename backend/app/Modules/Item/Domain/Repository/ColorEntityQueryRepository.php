<?php

namespace App\Modules\Item\Domain\Repository;

interface ColorEntityQueryRepository
{
     public function resolveCanonicalByEntityId(int $entityId): int;

    public function resolveCanonicalByName(string $input): ?int;
}