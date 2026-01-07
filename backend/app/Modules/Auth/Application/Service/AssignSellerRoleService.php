<?php

namespace App\Modules\Auth\Application\Service;

use Illuminate\Support\Facades\DB;

final class AssignSellerRoleService
{
    public function assignIndividualIfNotExists(int $userId): void
    {
        $exists = DB::table('role_user')
            ->where('user_id', $userId)
            ->whereNull('shop_id')
            ->where('role_id', $this->sellerRoleId())
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('role_user')->insert([
            'user_id' => $userId,
            'role_id' => $this->sellerRoleId(),
            'shop_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sellerRoleId(): int
    {
        return DB::table('roles')
            ->where('slug', 'seller')
            ->value('id');
    }
}
