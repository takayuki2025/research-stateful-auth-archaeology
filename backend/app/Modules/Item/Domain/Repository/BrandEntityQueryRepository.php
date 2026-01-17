<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

interface BrandEntityQueryRepository
{
    /**
     * entity_id → canonical entity
     */
    public function resolveCanonicalByEntityId(int $entityId): int;

    /**
     * input name → canonical entity
     */
    public function resolveCanonicalByName(string $input): ?int;
}
