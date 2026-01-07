<?php

namespace App\Modules\Shop\Application\UseCase;

use App\Modules\User\Domain\Repository\ProfileRepository;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Shop\Domain\Repository\ShopAddressRepository;
use App\Modules\Order\Domain\ValueObject\Address;
use App\Modules\Shop\Domain\Entity\ShopAddress;

final class EnsureShopAddressFromProfileUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
        private ShopRepository $shops,
        private ShopAddressRepository $addresses,
    ) {
    }

    public function handle(int $userId): void
    {
        $profile = $this->profiles->findByUserId($userId);
        if (! $profile) {
            return;
        }

        $shop = $this->shops->findByOwnerUserId($userId);
        if (! $shop) {
            return;
        }

        if ($this->addresses->findDefaultByShopId($shop->id())) {
            return;
        }

        $address = Address::fromArray([
            'postal_code'    => $profile->postNumber(),
            'prefecture'     => null,
            'city'           => null,
            'address_line1'  => $profile->address(),
            'address_line2'  => $profile->building(),
            'recipient_name' => $profile->displayName(), // ★ ここも重要
            'phone'          => null,
        ]);

        $shopAddress = ShopAddress::createDefault(
            shopId: $shop->id(),
            address: $address
        );

        $this->addresses->save($shopAddress);
    }
}
