<?php

namespace App\Modules\Comment\Domain\Entity;

use App\Modules\Comment\Domain\ValueObject\{
    CommentId,
    CommentBody,
    CommentAuthorId,
    CommentTargetId
};

final class Comment
{
    public function __construct(
        private readonly CommentId $id,
        private readonly CommentAuthorId $authorId,
        private readonly CommentTargetId $targetId,
        private readonly CommentBody $body,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public function id(): CommentId
    {
        return $this->id;
    }
    public function authorId(): CommentAuthorId
    {
        return $this->authorId;
    }
    public function targetId(): CommentTargetId
    {
        return $this->targetId;
    }
    public function body(): CommentBody
    {
        return $this->body;
    }
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
