<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ItemDraftSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('item_drafts')->insert([
            'id' => (string) Str::uuid(),

            // 🔑 SoT: 誰が出品したか
            'seller_id' => 'individual:1',

            // 補助参照
            'user_id' => 1,
            'shop_id' => null,

            // 🔹 人間入力の事実（正規化しない）
            'name'      => 'テスト商品',
            'price'     => 1000,
            'brand'     => 'ふじふぃるむ',
            'condition' => '新品',
            'category'  => json_encode(['カメラ'], JSON_UNESCAPED_UNICODE),

            'item_image' => null,
            'explain'    => '初期ドラフト',
            'remain'     => 1,

            'status' => 'draft',

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}