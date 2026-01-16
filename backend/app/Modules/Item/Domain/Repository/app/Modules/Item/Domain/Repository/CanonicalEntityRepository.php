<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

interface CanonicalEntityRepository
{
    /**
     * @return array<int, array{id:int, canonical_name:string, display_name:string}>
     */
    public function listByType(string $type): array;
}