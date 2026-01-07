<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopAddressesTableSeeder extends Seeder
{
    public function run(): void
    {
        // shop_id 昇順
        $shops = DB::table('shops')
            ->orderBy('id')
            ->get();

        // user_id 昇順（primary のみ）
        $userAddresses = DB::table('user_addresses')
            ->where('is_primary', true)
            ->orderBy('user_id')
            ->get();

        foreach ($shops as $index => $shop) {
            if (!isset($userAddresses[$index])) {
                Log::warning("No matching user_address for shop_id={$shop->id}");
                continue;
            }

            $ua = $userAddresses[$index];

            // 冪等
            DB::table('shop_addresses')
                ->where('shop_id', $shop->id)
                ->delete();

            $address = [
                'postal_code'     => $ua->post_number,
                'prefecture'      => $ua->prefecture,
                'city'            => $ua->city,
                'address_line1'   => $ua->address_line1,
                'address_line2'   => $ua->address_line2,
                'recipient_name'  => $ua->recipient_name,
                'phone'           => $ua->phone,
            ];

            DB::table('shop_addresses')->insert([
                'shop_id'    => $shop->id,
                'address'    => json_encode($address, JSON_UNESCAPED_UNICODE),
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Shop address seeded: shop_id={$shop->id} ← user_id={$ua->user_id}");
        }
    }
}
