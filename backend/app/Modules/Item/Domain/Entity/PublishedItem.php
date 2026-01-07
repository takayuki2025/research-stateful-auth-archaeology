<?php

namespace App\Modules\Item\Domain\Entity;

use App\Modules\Item\Domain\ValueObject\{
    ItemId,
    SellerId,
    ItemName,
    Money,
    ItemStatus,
    Brand,
    CategoryList,
    ItemImagePath,
    StockCount
};

final class PublishedItem
{
    private function __construct(
        private ItemId $id,
        private SellerId $sellerId,
        private ItemName $name,
        private Money $price,
        private ?Brand $brand,
        private string $explain,
        private string $condition,
        private CategoryList $category,
        private ItemImagePath $imagePath,
        private StockCount $remain,
        private ItemStatus $status,
    ) {}

    public static function fromDraft(
        ItemId $id,
        ItemDraft $draft
    ): self {
        return new self(
            $id,
            $draft->sellerId(),
            $draft->name(),
            $draft->price(),
            $draft->normalizedBrand(),
            $draft->explain(),        // Draft 側に getter 追加前提
            $draft->condition(),      // 同上
            $draft->category(),       // 同上
            $draft->itemImage(),      // ★画像必須
            $draft->remain(),         // 在庫
            ItemStatus::PUBLISHED
        );
    }

    // === getters（Repository 用）===

    public function sellerId(): SellerId { return $this->sellerId; }
    public function name(): ItemName { return $this->name; }
    public function price(): Money { return $this->price; }
    public function brand(): ?Brand { return $this->brand; }
    public function explain(): string { return $this->explain; }
    public function condition(): string { return $this->condition; }
    public function category(): CategoryList { return $this->category; }
    public function imagePath(): ItemImagePath { return $this->imagePath; }
    public function remain(): StockCount { return $this->remain; }
}