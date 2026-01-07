<?php

namespace App\Modules\Item\Application\UseCase\Favorite;

use App\Modules\Item\Domain\Repository\ItemRepository;

class ListFavoriteUseCase
{
    public function __construct(
        private readonly ItemRepository $items
    ) {
    }

    /**
     * お気に入り一覧（ユーザーごと）
     */
    public function execute(int $userId): iterable
    {
        return $this->items->listByCartUser($userId);
    }
}
