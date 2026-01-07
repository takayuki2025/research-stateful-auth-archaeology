<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // =========================
            // Public / Customer
            // =========================
            ['name' => 'Customer', 'slug' => 'customer'],

            // =========================
            // Seller / Shop
            // =========================
            ['name' => 'Seller', 'slug' => 'seller'],          // ★ 追加（今回の本命）
            ['name' => 'Shop Owner', 'slug' => 'owner'],
            ['name' => 'Shop Manager', 'slug' => 'manager'],
            ['name' => 'Shop Staff', 'slug' => 'staff'],

            // =========================
            // Domain / Admin
            // =========================
            ['name' => 'Domain Lead Admin', 'slug' => 'domain_lead_admin'],
            ['name' => 'Supervisor Admin', 'slug' => 'supervisor_admin'],
            ['name' => 'System Manager Admin', 'slug' => 'system_manager_admin'],

            // =========================
            // Developer
            // =========================
            ['name' => 'Top Developer', 'slug' => 'top_developer'],
            ['name' => 'Principle Developer', 'slug' => 'principle_developer'],
            ['name' => 'Developer', 'slug' => 'developer'],
        ];

        $now = now();

        $rolesWithTimestamps = array_map(
            fn ($role) => array_merge($role, [
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            $roles
        );

        // 🔥 初期開発フェーズ用：完全初期化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('roles')->insert($rolesWithTimestamps);
    }
}
