<?php

namespace App\Modules\Order\Application\Dto;

final class CreateOrderInput
{
    /**
     * @param array<int, array{
     *   item_id:int,
     *   name:string,
     *   price_amount:int,
     *   price_currency:string,
     *   condition?:string|null,
     *   category?:array,
     *   image_path?:string|null,
     *   quantity?:int
     * }> $items
     */
    public function __construct(
        public readonly int $shopId,
        public readonly int $userId,
        public readonly array $items,
        public readonly ?array $meta = null,
    ) {
    }
}
