<?php

namespace App\Modules\Shop\Infrastructure\Persistence;

use App\Models\Shop as ShopModel;
use App\Models\ShopAddress as ShopAddressModel;
use App\Modules\Shop\Domain\Entity\Shop;
use App\Modules\Shop\Domain\Enum\ShopStatus;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Order\Domain\ValueObject\Address; // ★暫定
use Illuminate\Support\Facades\DB;
use App\Models\Shop as EloquentShop;


use Illuminate\Database\Eloquent\ModelNotFoundException;
final class EloquentShopRepository implements ShopRepository
{
    public function save(Shop $shop): Shop
    {
        $model = $shop->id()
            ? ShopModel::findOrFail($shop->id())
            : new ShopModel();

        $model->shop_code      = $shop->shopCode();
        $model->owner_user_id  = $shop->ownerUserId();
        $model->name           = $shop->name();
        $model->status         = $shop->status()->value;
        $model->save();

        // ★発送元住所がセットされているなら upsert（Aフェーズ最小）
        // ※ shippingAddress 未設定でも Shop は保存できる。購入/発送の手前でガードする想定。
        try {
            $addr = $shop->shippingAddress();
            ShopAddressModel::updateOrCreate(
                ['shop_id' => $model->id],
                $addr->toArray()
            );
        } catch (\DomainException $e) {
            // shippingAddress not set → 住所未登録として許容（ここで throw しない）
        }

        $model->load('shippingAddress');

        return $this->toEntity($model);
    }

    public function findByOwnerUserId(int $userId): ?Shop
    {
        $model = ShopModel::with('shippingAddress')
            ->where('owner_user_id', $userId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findById(int $shopId): ?Shop
    {
        $model = ShopModel::with('shippingAddress')->find($shopId);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByShopCode(string $shopCode): ?Shop
    {
        $model = ShopModel::with('shippingAddress')
            ->where('shop_code', $shopCode)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    private function toEntity(ShopModel $model): Shop
    {
        $address = null;

        if ($model->shippingAddress) {
            $address = Address::fromArray([
                'postal_code'    => $model->shippingAddress->postal_code,
                'prefecture'     => $model->shippingAddress->prefecture,
                'city'           => $model->shippingAddress->city,
                'address_line1'  => $model->shippingAddress->address_line1,
                'address_line2'  => $model->shippingAddress->address_line2,
                'recipient_name' => $model->shippingAddress->recipient_name,
                'phone'          => $model->shippingAddress->phone,
            ]);
        }

        return new Shop(
            id: $model->id,
            shopCode: $model->shop_code,
            ownerUserId: $model->owner_user_id,
            name: $model->name,
            status: ShopStatus::from($model->status),
            shippingAddress: $address,
        );
    }

    public function ensureDefaultShippingAddress(
        int $shopId,
        Address $address
    ): void {
        // Aフェーズ：1ショップ1住所なので delete → insert
        DB::table('shop_addresses')
            ->where('shop_id', $shopId)
            ->delete();

        DB::table('shop_addresses')->insert([
            'shop_id'        => $shopId,
            'postal_code'    => $address->postalCode(),
            'prefecture'     => $address->prefecture(),
            'city'           => $address->city(),
            'address_line1'  => $address->addressLine1(),
            'address_line2'  => $address->addressLine2(),
            'recipient_name' => $address->recipientName(),
            'phone'          => $address->phone(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function findByCodeOrFail(string $shopCode): Shop
    {
        $model = ShopModel::where('shop_code', $shopCode)->first();

        if (! $model) {
            throw new ModelNotFoundException("Shop not found: {$shopCode}");
        }

        // ★ Eloquent → Domain 変換
        return new Shop(
    id: $model->id,
    shopCode: $model->shop_code,
    ownerUserId: $model->owner_user_id,
    name: $model->name,
    status: ShopStatus::from($model->status), // ★ ここが重要
);
    }
}