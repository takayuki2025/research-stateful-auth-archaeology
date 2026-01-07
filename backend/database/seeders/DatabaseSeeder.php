<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {





        $this->call([
            RoleSeeder::class,
            UsersTableSeeder::class,
            UserAddressesTableSeeder::class,
            ProfilesTableSeeder::class,
            ShopsTableSeeder::class,
            ShopAddressesTableSeeder::class,
            RoleUserSeeder::class,
            ItemsTableSeeder::class,
            BrandEntitySeeder::class,
            ConditionEntitySeeder::class,
            ColorEntitySeeder::class,
        ]);




    }
}
