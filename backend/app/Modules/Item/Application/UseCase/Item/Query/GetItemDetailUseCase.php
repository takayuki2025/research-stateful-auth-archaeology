<?php

namespace App\Modules\Item\Application\UseCase\Item\Query;

use App\Modules\Item\Application\Dto\Item\ItemDetailOutputDto;
use App\Modules\Item\Application\Service\ItemDetailReadService;
use App\Modules\Reaction\Application\UseCase\Query\IsFavoritedUseCase;
use App\Modules\Reaction\Application\UseCase\Query\CountFavoritesUseCase;
use App\Modules\Comment\Application\UseCase\Query\ListItemCommentsUseCase;

final class GetItemDetailUseCase
{
    public function __construct(
        private readonly ItemDetailReadService $itemReader,
        private readonly IsFavoritedUseCase $isFavorited,
        private readonly CountFavoritesUseCase $countFavorites,
        private readonly ListItemCommentsUseCase $listComments,
    ) {
    }

    public function execute(
        int $itemId,
        ?int $viewerUserId
    ): ItemDetailOutputDto {

        // 🔍 Detail ReadModel（NotFound は Service に委譲）
        $item = $this->itemReader->get($itemId);

        // 💬 コメント
        $comments = $this->listComments->execute($itemId);

        // ❤️ お気に入り
        $isFavorited = $viewerUserId !== null
            ? $this->isFavorited->execute($viewerUserId, $itemId)
            : false;

        // ❤️ お気に入り数
        $favoritesCount = $this->countFavorites->execute($itemId);

        return new ItemDetailOutputDto(
            item: $item,
            comments: $comments,
            isFavorited: $isFavorited,
            favoritesCount: $favoritesCount,
        );
    }
}