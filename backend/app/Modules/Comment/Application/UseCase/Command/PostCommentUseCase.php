<?php

namespace App\Modules\Comment\Application\UseCase\Command;

use App\Modules\Comment\Domain\Repository\CommentRepository;
use App\Modules\Comment\Domain\Entity\Comment;
use App\Modules\Comment\Domain\ValueObject\{
    CommentId,
    CommentAuthorId,
    CommentTargetId,
    CommentBody
};

final class PostCommentUseCase
{
    public function __construct(
        private readonly CommentRepository $comments
    ) {
    }

    public function execute(
        int $userId,
        int $itemId,
        string $body
    ): array {
        $comment = new Comment(
            id: new CommentId(0),
            authorId: new CommentAuthorId($userId),
            targetId: new CommentTargetId($itemId),
            body: new CommentBody($body),
            createdAt: new \DateTimeImmutable(),
        );

        $saved = $this->comments->save($comment);

        return [
            'id' => $saved->id()->value(),
            'comment' => $saved->body()->value(),
            'created_at' => $saved->createdAt()->format('Y-m-d H:i:s'),
        ];
    }
}
