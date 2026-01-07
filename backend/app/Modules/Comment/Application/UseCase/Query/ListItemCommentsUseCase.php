<?php

namespace App\Modules\Comment\Application\UseCase\Query;

use App\Modules\Comment\Application\Dto\CommentViewDto;
use App\Modules\Comment\Domain\Repository\CommentRepository;

final class ListItemCommentsUseCase
{
    public function __construct(
        private readonly CommentRepository $comments
    ) {
    }

    public function execute(int $itemId): array
    {
        $rows = $this->comments->listByItemId($itemId);

        return array_map(
            fn ($row) => CommentViewDto::from(
                $row['comment'],
                $row['user']
            ),
            $rows
        );
    }
}
