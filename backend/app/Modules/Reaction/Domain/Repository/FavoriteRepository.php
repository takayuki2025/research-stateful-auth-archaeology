<?php

namespace App\Modules\Reaction\Domain\Repository;

use App\Modules\Reaction\Domain\Entity\Favorite;
use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

interface FavoriteRepository
{
    public function add(Favorite $favorite): void;
    public function remove(ReactorId $reactorId, FavoriteTargetId $targetId): void;

    public function exists(ReactorId $reactorId, FavoriteTargetId $targetId): bool;

    public function countByTarget(FavoriteTargetId $targetId): int;

    /**
     * @return iterable<mixed> 既存実装に合わせ、Itemの一覧相当を返す（DTO化は Application で行う）
     */
    public function listItemsByUser(ReactorId $reactorId): iterable;
}
