<?php

namespace App\Modules\Shop\Infrastructure\Persistence;

use App\Modules\Shop\Domain\Repository\ShopQueryRepository;
use App\Modules\Shop\Domain\Entity\Shop;
use App\Models\Shop as ShopModel;
use App\Modules\Shop\Domain\Enum\ShopStatus;


final class EloquentShopQueryRepository implements ShopQueryRepository
{
    public function findByCode(string $shopCode): ?Shop
    {
        $row = ShopModel::where('shop_code', $shopCode)->first();

        if (!$row) {
            return null;
        }

        return new Shop(
            id: $row->id,
            shopCode: $row->shop_code,
            name: $row->name,
            status: ShopStatus::from($row->status), // ★ ここが本命
            ownerUserId: $row->owner_user_id,
        );
    }
}
