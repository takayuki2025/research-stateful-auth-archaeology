<?php

namespace App\Modules\Comment\Infrastructure\Persistence;

use App\Models\Comment as EloquentComment;
use App\Modules\Comment\Domain\Entity\Comment;
use App\Modules\Comment\Domain\Repository\CommentRepository;
use App\Modules\Comment\Domain\ValueObject\CommentId;
use App\Modules\Comment\Domain\ValueObject\CommentBody;
use App\Modules\Comment\Domain\ValueObject\CommentAuthorId;
use App\Modules\Comment\Domain\ValueObject\CommentTargetId;
use DateTimeImmutable;

final class EloquentCommentRepository implements CommentRepository
{
    public function save(Comment $comment): Comment
    {
        $model = new EloquentComment();

        $model->user_id = $comment->authorId()->value();
        $model->item_id = $comment->targetId()->value();
        $model->comment = $comment->body()->value();

        $model->save();


        return new Comment(
            id: new CommentId($model->id),
            authorId: new CommentAuthorId($model->user_id),
            targetId: new CommentTargetId($model->item_id),
            body: new CommentBody($model->comment),
            createdAt: DateTimeImmutable::createFromMutable($model->created_at)
        );

    }

    public function listByItemId(int $itemId): array
    {
        return EloquentComment::with('user')
            ->where('item_id', $itemId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (EloquentComment $m) => [
                'comment' => new Comment(
                    id: new CommentId($m->id),
                    authorId: new CommentAuthorId($m->user_id),
                    targetId: new CommentTargetId($m->item_id),
                    body: new CommentBody($m->comment),
                    createdAt: DateTimeImmutable::createFromMutable($m->created_at)
                ),
                'user' => $m->user,
            ])
            ->all();
    }
}
