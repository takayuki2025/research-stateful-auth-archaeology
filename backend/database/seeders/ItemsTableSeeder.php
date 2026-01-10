<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Shop;
use App\Models\Item;
use App\Modules\Item\Domain\Service\AtlasKernelService;

final class ItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        /* =====================================================
         * 0. 初期化
         * ===================================================== */
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('items')->truncate();
        DB::table('item_entities')->truncate();
        DB::table('item_entity_tags')->truncate();
        DB::table('item_entity_audits')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /* =====================================================
         * 1. shop 解決
         * ===================================================== */
        $shops = Shop::orderBy('id')->pluck('id')->values();

        /* =====================================================
         * 2. user 解決
         * ===================================================== */
        $userEmails = [
            'valid.email@example.com',
            'taro.y@coachtech.com',
            'reina.n@coachtech.com',
            'tomomi.a@coachtech.com',
        ];

        $userIds = User::whereIn('email', $userEmails)
            ->pluck('id', 'email')
            ->toArray();

        $fallbackUserId = $userIds['valid.email@example.com'] ?? User::min('id');

        /* =====================================================
         * 3. Seeder データ（※一切変更なし）
         * ===================================================== */
        $items = [
            // shop 1
            [
                'email' => 'valid.email@example.com',
                'shop' => 0,
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'explain' => 'スタイリッシュなデザインのメンズ腕時計',
                'condition' => '良好',
                'category' => ['メンズ'],
                'image' => 'item_images/Armani+Mens+Clock.jpg',
                'published_at' => now()
            ],
            [
                'email' => 'valid.email@example.com',
                'shop' => 0,
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'explain' => '高速で信頼性の高いハードディスク',
                'condition' => '目立った傷や汚れなし',
                'category' => ['家電'],
                'image' => 'item_images/HDD+Hard+Disk.jpg',
                'published_at' => now()
            ],

            // shop 2
            [
                'email' => 'taro.y@coachtech.com',
                'shop' => 1,
                'name' => '玉ねぎ３束',
                'price' => 300,
                'brand' => 'なし',
                'explain' => '新鮮な玉ねぎ3束のセット',
                'condition' => 'やや傷や汚れあり',
                'category' => ['キッチン'],
                'image' => 'item_images/iLoveIMG+d.jpg',
                'published_at' => now()
            ],
            [
                'email' => 'taro.y@coachtech.com',
                'shop' => 1,
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'explain' => 'クラシックなデザインの革靴',
                'condition' => '状態が悪い',
                'category' => ['メンズ'],
                'image' => 'item_images/Leather+Shoes+Product+Photo.jpg',
                'published_at' => now()
            ],

            // shop 3
            [
                'email' => 'reina.n@coachtech.com',
                'shop' => 2,
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'explain' => '高性能なノートパソコン',
                'condition' => '良好',
                'category' => ['家電'],
                'image' => 'item_images/Living+Room+Laptop.jpg',
                'published_at' => now()
            ],
            [
                'email' => 'reina.n@coachtech.com',
                'shop' => 2,
                'name' => 'マイク',
                'price' => 8000,
                'brand' => 'なし',
                'explain' => '高音質のレコーディング用マイク',
                'condition' => '目立った傷や汚れなし',
                'category' => ['家電'],
                'image' => 'item_images/Music+Mic+4632231.jpg',
                'published_at' => now()
            ],
            [
                'email' => 'reina.n@coachtech.com',
                'shop' => 2,
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'explain' => 'おしゃれなショルダーバッグ',
                'condition' => 'やや傷や汚れあり',
                'category' => ['レディース'],
                'image' => 'item_images/Purse+fashion+pocket.jpg',
                'published_at' => now()
            ],

            // shop 4
            [
                'email' => 'tomomi.a@coachtech.com',
                'shop' => 3,
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => 'なし',
                'explain' => '使いやすいタンブラー',
                'condition' => '状態が悪い',
                'category' => ['キッチン'],
                'image' => 'item_images/Tumbler+souvenir.jpg',
                'published_at' => now()
            ],
            [
                'email' => 'tomomi.a@coachtech.com',
                'shop' => 3,
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'explain' => '手動のコーヒーミル',
                'condition' => '良好',
                'category' => ['キッチン'],
                'image' => 'item_images/Waitress+with+Coffee+Grinder.jpg',
                'published_at' => now()
            ],
            [
                'email' => 'tomomi.a@coachtech.com',
                'shop' => 3,
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'explain' => '便利なメイクアップセット',
                'condition' => '目立った傷や汚れなし',
                'category' => ['レディース'],
                'image' => 'item_images/外出メイクアップセット.jpg',
                'published_at' => now()
            ],
        ];

        /* =====================================================
         * 4. Eloquent + AtlasKernel
         * ===================================================== */
        $atlasKernel = app(AtlasKernelService::class);

        foreach ($items as $data) {

            /**
             * ★ 出品起源判定（新規追加）
             */
            $isSeederItem = $data['shop'] !== null;

            $itemOrigin = $isSeederItem
                ? 'SHOP_MANAGED'
                : 'USER_PERSONAL';

            /**
             * ★ 個人出品者（Seeder は必ず null）
             */
            $createdByUserId = $isSeederItem
                ? null
                : ($userIds[$data['email']] ?? $fallbackUserId);

            /**
             * shop_id 解決（既存ロジック維持）
             */
            $shopId = null;
            if ($isSeederItem && isset($shops[$data['shop']])) {
                $shopId = $shops[$data['shop']];
            }

            /**
             * ★ Item 作成（item_origin 追加）
             */
            $item = Item::create([
                'item_origin'        => $itemOrigin,
                'created_by_user_id' => $createdByUserId,
                'shop_id'            => $shopId,

                'name'       => $data['name'],
                'price'      => $data['price'],
                'brand'      => $data['brand'],
                'explain'    => $data['explain'],
                'condition'  => $data['condition'],
                'category'   => json_encode($data['category'], JSON_UNESCAPED_UNICODE),
                'item_image' => $data['image'],
                'remain'     => 1,
                'published_at' => $data['published_at'],
            ]);

            Log::info('[Seeder][ItemCreated]', [
                'item_id'             => $item->id,
                'item_origin'         => $item->item_origin,
                'shop_id'             => $item->shop_id,
                'created_by_user_id'  => $item->created_by_user_id,
                'is_seeder'           => $isSeederItem,
            ]);

            /**
             * AtlasKernel（既存）
             */
            // $atlasKernel->analyze(
            //     itemId: $item->id,
            //     rawText: $item->brand ?? '',
            //     tenantId: null
            // );
        }

        Log::info('[ItemsSeeder] completed with AtlasKernel.');
    }
}
