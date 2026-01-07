<?php

namespace App\Modules\Item\Application\Dto\Item;

final class ItemDetailOutputDto
{
    /**
     * @param array{
     *   id:int,
     *   name:string,
     *   price:int,
     *   item_image:string|null,
     *   brand_primary?:string|null,
     *   condition_name?:string|null,
     *   color_name?:string|null,
     *   remain:int,
     *   user_id:int
     * } $item
     * @param iterable $comments
     */
    public function __construct(
        public readonly array $item,
        public readonly iterable $comments,
        public readonly bool $isFavorited,
        public readonly int $favoritesCount,
    ) {
    }
}
