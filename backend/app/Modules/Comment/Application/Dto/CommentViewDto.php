<?php

namespace App\Modules\Comment\Application\Dto;

use App\Modules\Comment\Domain\Entity\Comment;
use App\Models\User;

final class CommentViewDto
{
    public function __construct(
        public int $id,
        public string $comment,
        public string $created_at,
        public array $user,
    ) {
    }

    public static function from(Comment $comment, User $user): self
    {
        return new self(
            id: $comment->id()->value(),
            comment: $comment->body()->value(),
            created_at: $comment->createdAt()->format('Y-m-d H:i:s'),
            user: [
                'id' => $user->id,
                'name' => $user->name,
                'user_image' => $user->user_image,
            ]
        );
    }
}
