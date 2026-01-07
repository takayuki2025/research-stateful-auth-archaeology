<?php

namespace App\Modules\Shop\Domain\Policy;

use App\Modules\Shop\Domain\Repository\ShopRoleQueryRepository;

final class ShopRolePolicy
{
    public function __construct(
        private ShopRoleQueryRepository $roles,
    ) {
    }

    /**
     * @return string[] role slugs
     */
    public function rolesFor(int $userId, int $shopId): array
    {
        return $this->roles->getRoleSlugsForUserInShop($userId, $shopId);
    }

    public function canAccessShop(int $userId, int $shopId): bool
    {
        // 最小：ショップスコープロールがあるならアクセス可
        // グローバルロール（shop_id null）を許可したい場合は rolesFor に含まれるため、ここも自然に true になり得る
        $slugs = $this->rolesFor($userId, $shopId);
        return count($slugs) > 0;
    }

    public function canManageShop(int $userId, int $shopId): bool
    {
        $slugs = $this->rolesFor($userId, $shopId);
        return in_array('owner', $slugs, true) || in_array('manager', $slugs, true);
    }

    public function canManageOrders(int $userId, int $shopId): bool
    {
        $slugs = $this->rolesFor($userId, $shopId);
        return in_array('owner', $slugs, true)
            || in_array('manager', $slugs, true)
            || in_array('staff', $slugs, true);
    }

    public function canRefund(int $userId, int $shopId): bool
    {
        $slugs = $this->rolesFor($userId, $shopId);
        return in_array('owner', $slugs, true) || in_array('manager', $slugs, true);
    }
}
