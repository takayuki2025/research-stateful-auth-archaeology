<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            [
                'user_id' => 1,
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'explain' => 'スタイリッシュなデザインのメンズ腕時計',
                'condition' => '良好',
                'category' => json_encode(['メンズ']),
                'item_image' => 'storage/item_images/Armani+Mens+Clock.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 1,
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'explain' => '高速で信頼性の高いハードディスク',
                'condition' => '目立った傷や汚れなし',
                'category' => json_encode(['家電']),
                'item_image' => 'storage/item_images/HDD+Hard+Disk.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 2,
                'name' => '玉ねぎ３束',
                'price' => 300,
                'brand' => 'なし',
                'explain' => '新鮮な玉ねぎ3束のセット',
                'condition' => 'やや傷や汚れあり',
                'category' => json_encode(['キッチン']),
                'item_image' => 'storage/item_images/iLoveIMG+d.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 2,
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'explain' => 'クラシックなデザインの革靴',
                'condition' => '状態が悪い',
                'category' => json_encode(['メンズ']),
                'item_image' => 'storage/item_images/Leather+Shoes+Product+Photo.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 3,
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'explain' => '高性能なノートパソコン',
                'condition' => '良好',
                'category' => json_encode(['家電']),
                'item_image' => 'storage/item_images/Living+Room+Laptop.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 3,
                'name' => 'マイク',
                'price' => 8000,
                'brand' => 'なし',
                'explain' => '高音質のレコーディング用マイク',
                'condition' => '目立った傷や汚れなし',
                'category' => json_encode(['家電']),
                'item_image' => 'storage/item_images/Music+Mic+4632231.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 3,
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'explain' => 'おしゃれなショルダーバッグ',
                'condition' => 'やや傷や汚れあり',
                'category' => json_encode(['レディース']),
                'item_image' => 'storage/item_images/Purse+fashion+pocket.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 4,
                'name' => 'ダンブラー',
                'price' => 500,
                'brand' => 'なし',
                'explain' => '使いやすいダンブラー',
                'condition' => '状態が悪い',
                'category' => json_encode(['キッチン']),
                'item_image' => 'storage/item_images/Tumbler+souvenir.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 4,
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'slarbacks',
                'explain' => '手動のコーヒーミル',
                'condition' => '良好',
                'category' => json_encode(['キッチン']),
                'item_image' => 'storage/item_images/Waitress+with+Coffee+Grinder.jpg',
                'remain' => 1,
            ],
            [
                'user_id' => 4,
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'explain' => '便利なメイクアップセット',
                'condition' => '目立った傷や汚れなし',
                'category' => json_encode(['レディース']),
                'item_image' => 'storage/item_images/外出メイクアップセット.jpg',
                'remain' => 1,
            ],
        ];

        DB::table('items')->insert($params);
    }
}