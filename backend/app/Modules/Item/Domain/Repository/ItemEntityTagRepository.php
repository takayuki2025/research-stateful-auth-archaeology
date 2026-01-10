<?php

namespace App\Modules\Item\Domain\Repository;

interface ItemEntityTagRepository
{
    public function replaceTags(
        int $itemEntityId,
        string $tagType,
        array $tags
    ): void;

    public function findLatestByItemId(int $itemId): array;
}