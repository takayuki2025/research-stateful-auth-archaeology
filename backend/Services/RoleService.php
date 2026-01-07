<?php

namespace App\Services;

class RoleService
{
    public static function assignRole(User $user, string $roleName, ?int $shopId = null)
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->syncWithoutDetaching([
            $role->id => ['shop_id' => $shopId]
        ]);
    }

    public static function hasRole(User $user, string $roleName, ?Shop $shop = null)
    {
        $query = $user->roles()->where('name', $roleName);

        if ($shop) {
            $query->wherePivot('shop_id', $shop->id);
        }

        return $query->exists();
    }
}
