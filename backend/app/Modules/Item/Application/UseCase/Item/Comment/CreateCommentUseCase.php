<?php

namespace App\Modules\Item\Application\UseCase\Item\Comment;

use App\Modules\Item\Domain\Repository\ItemRepository;

class CreateCommentUseCase
{
    public function __construct(
        private readonly ItemRepository $items
    ) {
    }

    public function execute(int $userId, int $itemId, string $comment): array
    {
        return $this->items->createComment(
            userId: $userId,
            itemId: $itemId,
            comment: $comment
        );
    }
}
