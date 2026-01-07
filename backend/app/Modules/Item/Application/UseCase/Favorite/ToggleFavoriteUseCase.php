<?php

namespace App\Modules\Item\Application\UseCase\Favorite;

use App\Modules\Item\Domain\Repository\ItemRepository;

class ToggleFavoriteUseCase
{
    public function __construct(
        private readonly ItemRepository $items
    ) {
    }

    /**
     * @param int $userId
     * @param int $itemId
     * @param bool $add true=追加 / false=削除
     */
    public function execute(int $userId, int $itemId, bool $add): array
    {
        // Repository 側の toggleMylist を利用
        $this->items->toggleMylist($userId, $itemId);

        return [
            'favorited' => $this->items->isFavorited($itemId, $userId),
            'favorites_count' => $this->items->favoritesCount($itemId)
        ];
    }
}
