<?php

namespace App\Modules\Item\Application\Service;

use App\Modules\Item\Infrastructure\Persistence\Query\ItemReadRepository;
use App\Modules\Item\Domain\Exception\ItemNotFoundException;

final class ItemDetailReadService
{
    public function __construct(
        private readonly ItemReadRepository $items,
    ) {}

    /**
     * 商品詳細（v3 FIXED）
     * - 表示値は Repository を完全に信頼
     * - analysis_results は一切触らない
     */
    public function get(int $itemId): array
    {
        $row = $this->items->findWithDisplayEntities($itemId);

        if (! $row) {
            throw new ItemNotFoundException();
        }

        return $row;
    }
}