<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\Item as EloquentItem;
use App\Modules\Item\Domain\Entity\Item;
use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\ValueObject\{
    ItemId,
    Money,
    StockCount,
    CategoryList,
    ItemImagePath,
    ItemOrigin
};
use DateTimeImmutable;

final class EloquentItemRepository implements ItemRepository
{
    /* =====================================================
     * Eloquent → Domain（唯一の変換口）
     * ===================================================== */
    private function toDomain(EloquentItem $model): Item
    {
        $categories = $model->category ?? [];
        if (is_string($categories)) {
            $decoded = json_decode($categories, true);
            $categories = is_array($decoded) ? $decoded : [];
        }

        $imagePath = $model->item_image
            ? ItemImagePath::fromRaw($model->item_image)
            : null;

        $publishedAt = $model->published_at
            ? new DateTimeImmutable($model->published_at)
            : null;

        return Item::reconstitute(
            id: new ItemId((int) $model->id),
            itemOrigin: ItemOrigin::from((string) $model->item_origin),
            shopId: $model->shop_id !== null ? (int) $model->shop_id : null,
            createdByUserId: $model->created_by_user_id !== null
                ? (int) $model->created_by_user_id
                : null,
            name: (string) $model->name,
            price: new Money((int) $model->price, 'JPY'),
            explain: (string) ($model->explain ?? ''),
            condition: (string) ($model->condition ?? ''),
            category: new CategoryList($categories),
            itemImage: $imagePath,
            remain: new StockCount((int) ($model->remain ?? 0)),
            publishedAt: $publishedAt,
        );
    }

    /* =====================================================
     * Find
     * ===================================================== */
    public function findById(int $id): ?Item
    {
        $model = EloquentItem::query()->find($id);
        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @return Item[]
     */
    public function findPublicByShopId(int $shopId): array
{
    \Log::info('[Repo] findPublicByShopId called', [
        'shop_id' => $shopId,
    ]);

    $rows = EloquentItem::query()
        ->where('shop_id', $shopId)
        ->whereNotNull('published_at')
        ->orderByDesc('created_at')
        ->get();

    \Log::info('[Repo] items fetched', [
        'count' => $rows->count(),
    ]);

    return $rows
        ->map(fn (EloquentItem $row) => $this->toDomain($row))
        ->all();
}

    /* =====================================================
     * Save
     * ===================================================== */
    public function save(Item $item): void
    {
        $model = $item->getId()
            ? EloquentItem::query()->find($item->id())
            : new EloquentItem();

        $model->item_origin = $item->getItemOrigin()->value();
        $model->shop_id = $item->getShopId();
        $model->created_by_user_id = $item->getCreatedByUserId();
        $model->name = $item->getName();
        $model->price = $item->getPrice()->amount();
        $model->explain = $item->getExplain();
        $model->condition = $item->getCondition();
        $model->category = json_encode(
            $item->getCategory()->toArray(),
            JSON_UNESCAPED_UNICODE
        );
        $model->item_image = $item->getItemImage()
            ? $item->getItemImage()->value()
            : null;
        $model->remain = $item->getRemain()->toInt();
        $model->published_at = $item->getPublishedAt()
            ? $item->getPublishedAt()->format('Y-m-d H:i:s')
            : null;

        $model->save();

        if ($item->getId() === null) {
            $item->setId(new ItemId((int) $model->id));
        }
    }

    /* =====================================================
     * Delete
     * ===================================================== */
    public function delete(int $id): void
    {
        EloquentItem::query()->where('id', $id)->delete();
    }
}
