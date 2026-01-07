<?php

namespace App\Modules\Shop\Infrastructure\Persistence;

use App\Modules\Shop\Domain\Entity\ShopAddress;
use App\Modules\Shop\Domain\Repository\ShopAddressRepository;
use App\Modules\Order\Domain\ValueObject\Address;
use Illuminate\Support\Facades\DB;

final class EloquentShopAddressRepository implements ShopAddressRepository
{
    public function findDefaultByShopId(int $shopId): ?ShopAddress
    {
        $row = DB::table('shop_addresses')
            ->where('shop_id', $shopId)
            ->where('is_default', true)
            ->first();

        return $row ? $this->reconstitute($row) : null;
    }

    public function save(ShopAddress $address): ShopAddress
    {
        if ($address->id() === null) {
            $id = DB::table('shop_addresses')->insertGetId([
                'shop_id'    => $address->shopId(),
                'address'    => json_encode($address->address()->toArray(), JSON_UNESCAPED_UNICODE),
                'is_default' => $address->isDefault(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->findById($id);
        }

        DB::table('shop_addresses')
            ->where('id', $address->id())
            ->update([
                'address'    => json_encode($address->address()->toArray(), JSON_UNESCAPED_UNICODE),
                'is_default' => $address->isDefault(),
                'updated_at' => now(),
            ]);

        return $this->findById($address->id());
    }

    private function findById(int $id): ShopAddress
    {
        $row = DB::table('shop_addresses')->where('id', $id)->first();
        return $this->reconstitute($row);
    }

    private function reconstitute(object $row): ShopAddress
    {
        return ShopAddress::reconstitute(
            id: (int)$row->id,
            shopId: (int)$row->shop_id,
            address: Address::fromArray(json_decode($row->address, true)),
            isDefault: (bool)$row->is_default,
        );
    }
}
