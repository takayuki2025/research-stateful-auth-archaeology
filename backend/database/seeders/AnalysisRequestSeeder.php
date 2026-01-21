<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalysisRequestSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ seed専用 item を用意（なければ作る）
        $seedItemName = 'SEED_DUMMY__ANALYSIS_REQUEST';
        $seedItemId = DB::table('items')->where('name', $seedItemName)->value('id');

        if (! $seedItemId) {
            // shop_id は存在する最小を当てる（NULLでもよいが、あなたのitems設計に合わせて）
            $shopId = DB::table('shops')->min('id');

            $seedItemId = DB::table('items')->insertGetId([
                'item_origin'        => 'SHOP_MANAGED', // もしくは 'SEED_DUMMY' を許すならそれが最良
                'created_by_user_id' => null,
                'shop_id'            => $shopId,

                'name'        => $seedItemName,
                'price'       => 0,
                'brand'       => 'SEED_DUMMY',
                'explain'     => 'seed dummy item (do not show in normal UI)',
                'condition'   => 'N/A',
                'category'    => json_encode(['seed'], JSON_UNESCAPED_UNICODE),
                'item_image'  => null,
                'remain'      => 0,
                'published_at'=> null,

                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        DB::table('analysis_requests')->insert([
            'tenant_id'        => null,
            'item_id'          => $seedItemId, // ✅ item_id=1 固定をやめる
            'analysis_version' => 'v3_ai',
            'raw_text'         => 'SEED_DUMMY__RAW_TEXT',
            'payload_hash'     => hash('sha256', 'seed-dummy-payload-1'),
            'idempotency_key'  => 'seed_dummy_' . Str::uuid(),
            'status'           => 'done',
            'started_at'       => now()->subMinutes(2),
            'finished_at'      => now(),
            'retry_count'      => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}