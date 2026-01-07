<?php

namespace App\Modules\Comment\Domain\Repository;

use App\Modules\Comment\Domain\Entity\Comment;

interface CommentRepository
{
    /** 投稿 */
    public function save(Comment $comment): Comment;

    /** 商品ごとの一覧 */
    public function listByItemId(int $itemId): iterable;
}
