<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\Item;

interface ItemRepository
{
    public function findById(int $id): ?Item;

    /**
     * Upsert
     * - id === null : insert
     * - id !== null : update
     */
    public function save(Item $item): void;

    /**
     * Delete（論理 / 物理は運用次第）
     */
    public function delete(int $id): void;

    /**
     * @return Item[]
     */
    public function findPublicByShopId(int $shopId): array;
}
