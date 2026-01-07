<?php

namespace App\Modules\Reaction\Infrastructure\Persistence;

use App\Models\Good;
use App\Models\Item;
use App\Modules\Reaction\Domain\Entity\Favorite as FavoriteEntity;
use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class EloquentFavoriteRepository implements FavoriteRepository
{
    /**
     * Favorite を追加（冪等）
     */
    public function add(FavoriteEntity $favorite): void
    {
        Good::query()->firstOrCreate(
            [
                'user_id' => $favorite->reactorId()->value(),
                'item_id' => $favorite->targetId()->value(),
            ],
            [
                'shop_id' => $favorite->shopId(),
            ]
        );
    }

    /**
     * Favorite を削除
     */
    public function remove(ReactorId $reactorId, FavoriteTargetId $targetId): void
    {
        Good::query()
            ->where('user_id', $reactorId->value())
            ->where('item_id', $targetId->value())
            ->delete();
    }

    /**
     * Favorite が存在するか
     */
    public function exists(ReactorId $reactorId, FavoriteTargetId $targetId): bool
    {
        return Good::query()
            ->where('user_id', $reactorId->value())
            ->where('item_id', $targetId->value())
            ->exists();
    }

    /**
     * 対象 Item の Favorite 数
     */
    public function countByTarget(FavoriteTargetId $targetId): int
    {
        return Good::query()
            ->where('item_id', $targetId->value())
            ->count();
    }

    /**
     * ユーザーがお気に入りした Item 一覧
     *
     * @return iterable<Item>
     */
    public function listItemsByUser(ReactorId $reactorId): iterable
    {
        return Good::query()
            ->with('item')
            ->where('user_id', $reactorId->value())
            ->whereHas('item', function ($q) {
                $q->whereNotNull('published_at'); // ★ publish 済みのみ
            })
            ->latest()
            ->get()
            ->map(fn (Good $good) => $good->item)
            ->filter();
    }
}
