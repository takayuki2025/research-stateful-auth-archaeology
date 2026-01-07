<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('role_user')->truncate();

        $ownerRoleId = Role::where('slug', 'owner')->value('id');

        // ユーザーID = 1〜4 をそれぞれショップID = 1〜4 のオーナーにする
        foreach ([1, 2, 3, 4] as $i) {
            DB::table('role_user')->insert([
                'user_id' => $i,
                'role_id' => $ownerRoleId,
                'shop_id' => $i, // ★ user_id と shop_id を対応させる
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // domain_lead_admin（ユーザー5）
        $domainLeadAdminRoleId = Role::where('slug', 'domain_lead_admin')->value('id');
        $user5 = User::where('email', 't.principle.k2024@gmail.com')->first();

        if ($user5) {
            DB::table('role_user')->insert([
                'user_id' => $user5->id,
                'role_id' => $domainLeadAdminRoleId,
                'shop_id' => null, // ★ 全体管理者なので NULL
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
