<?php

namespace App\Modules\Search\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SearchItemResource extends JsonResource
{
    /**
     * @param array|object $resource
     */
    public function toArray($request): array
    {
        // $this は配列もオブジェクトも来得る（念のため両対応）
        $id = is_array($this->resource) ? ($this->resource['id'] ?? null) : ($this->resource->id ?? null);

        $shopId = is_array($this->resource) ? ($this->resource['shop_id'] ?? null) : ($this->resource->shop_id ?? null);
        $name = is_array($this->resource) ? ($this->resource['name'] ?? null) : ($this->resource->name ?? null);
        $priceAmount = is_array($this->resource) ? ($this->resource['price_amount'] ?? null) : ($this->resource->price_amount ?? null);
        $priceCurrency = is_array($this->resource) ? ($this->resource['price_currency'] ?? null) : ($this->resource->price_currency ?? null);
        $createdAt = is_array($this->resource) ? ($this->resource['created_at'] ?? null) : ($this->resource->created_at ?? null);

        return [
            'id' => $id,
            'shop_id' => $shopId,
            'name' => $name,
            'item_image_path' => $this->resource['item_image_path'],
            'price' => [
                'amount' => $priceAmount,
                'currency' => $priceCurrency,
            ],
            'created_at' => $createdAt,
        ];
    }
}