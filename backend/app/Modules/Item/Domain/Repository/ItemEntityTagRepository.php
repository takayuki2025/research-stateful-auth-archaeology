<?php

namespace App\Modules\Item\Domain\Repository;

interface ItemEntityTagRepository
{
    public function saveMany(
        int $itemEntityId,
        string $entityType,
        array $entities
    ): void;

    public function findByItemId(int $itemId): array;
}
