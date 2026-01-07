<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ItemDraft as EloquentItemDraft;
use App\Modules\Item\Domain\Entity\ItemDraft;
use App\Modules\Item\Domain\Repository\ItemDraftRepository;
use App\Modules\Item\Domain\ValueObject\{
    ItemDraftId,
    SellerId,
    ItemName,
    Money,
    BrandName,
    ItemStatus,
    CategoryList,
    ItemImagePath,
    StockCount
};

final class EloquentItemDraftRepository implements ItemDraftRepository
{
    public function nextIdentity(): ItemDraftId
    {
        return ItemDraftId::generate();
    }

    public function save(ItemDraft $draft): void
    {
        $model = EloquentItemDraft::find($draft->id()->value())
            ?? new EloquentItemDraft(['id' => $draft->id()->value()]);

        $model->seller_id  = $draft->sellerId()->raw();
        $model->shop_id    = $draft->shopId();
        $model->name       = $draft->name()->value();
        $model->price      = $draft->price()->amount();
        $model->brand      = $draft->brand()?->value();
        $model->status     = $draft->status()->value;
        $model->explain    = $draft->explain();
        $model->condition  = $draft->condition();
        $model->category   = $draft->category()->toArray();
        $model->remain     = $draft->remain()->toInt();
        $model->item_image = $draft->itemImage()?->value();

        $model->save();
    }

    public function findById(string $draftId): ?ItemDraft
    {
        $model = EloquentItemDraft::find($draftId);
        if (! $model) {
            return null;
        }

        return ItemDraft::reconstruct(
            id: ItemDraftId::fromString($model->id),
            sellerId: SellerId::fromRaw($model->seller_id),
            shopId: $model->shop_id,
            name: new ItemName($model->name),
            price: new Money($model->price, 'JPY'),
            brandRaw: $model->brand ? new BrandName($model->brand) : null,
            status: ItemStatus::from($model->status),
            explain: $model->explain ?? '',
            condition: $model->condition ?? '',
            category: $model->category ?? [],
            remain: new StockCount($model->remain),
            itemImage: $model->item_image
                ? ItemImagePath::fromRaw($model->item_image)
                : null,
        );
    }

    public function delete(string $draftId): void
    {
        EloquentItemDraft::where('id', $draftId)->delete();
    }
}
