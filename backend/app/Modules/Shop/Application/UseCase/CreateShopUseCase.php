<?php

namespace App\Modules\Shop\Application\UseCase;

use App\Modules\Shop\Application\Dto\CreateShopInput;
use App\Modules\Shop\Application\Dto\ShopOutput;
use App\Modules\Shop\Domain\Entity\Shop;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Order\Domain\ValueObject\Address; // ★暫定
use Illuminate\Support\Str;

final class CreateShopUseCase
{
    public function __construct(
        private ShopRepository $shops
    ) {
    }

    public function handle(CreateShopInput $input): ShopOutput
    {
        // ① 既存ショップガード
        $existing = $this->shops->findByOwnerUserId($input->ownerUserId);
        if ($existing) {
            throw new \DomainException('Shop already exists for this user.');
        }

        // ② shopCode 生成
        $shopCode = 'shop_' . Str::uuid()->toString();

        // ③ Shop Aggregate 生成
        $shop = Shop::create(
            ownerUserId: $input->ownerUserId,
            name: $input->name,
            shopCode: $shopCode,
        );

        // ④ 発送元住所を ValueObject 化
        $shippingAddress = new Address(
            postalCode: $input->postalCode,
            prefecture: $input->prefecture,
            city: $input->city,
            addressLine1: $input->addressLine1,
            addressLine2: $input->addressLine2,
            recipientName: $input->recipientName ?? $input->name, // 店舗名をデフォルトに
            phone: $input->phone,
        );

        // ⑤ Shop に発送元住所をセット
        $shop = $shop->withShippingAddress($shippingAddress);

        // ⑥ 永続化（shop + shop_addresses）
        $saved = $this->shops->save($shop);

        return ShopOutput::fromEntity($saved);
    }
}
