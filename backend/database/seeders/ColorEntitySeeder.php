<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ColorEntitySeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // Canonical definitions
        // =========================
        $canonicals = [
            'ブラック' => ['black', ['黒', 'くろ', 'クロ', 'black']],
            'ホワイト' => ['white', ['白', 'しろ', 'シロ', 'white']],
            'レッド'   => ['red', ['赤', 'あか', 'アカ', 'red']],
            'ブルー'   => ['blue', ['青', 'あお', 'アオ', 'blue']],
            'グリーン' => ['green', ['緑', 'みどり', 'ミドリ', 'green']],
            'イエロー' => ['yellow', ['黄', 'き', 'yellow']],
            'ブラウン' => ['brown', ['茶', 'ちゃ', 'brown']],
            'グレー'   => ['gray', ['gray', 'grey']],
        ];

        foreach ($canonicals as $canonicalName => [$normalizedKey, $aliases]) {
            // ① canonical（正規）
            DB::table('color_entities')->updateOrInsert(
                ['canonical_name' => $canonicalName],
                [
                    'display_name'   => $canonicalName,
                    'normalized_key' => $normalizedKey,
                    'is_primary'     => true,
                    'created_from'   => 'seed',
                ]
            );

            // canonical の id を取得
            $canonicalId = DB::table('color_entities')
                ->where('canonical_name', $canonicalName)
                ->value('id');

            // ② aliases（表記ゆれ）
            foreach ($aliases as $alias) {
                DB::table('color_entities')->updateOrInsert(
                    ['canonical_name' => $alias],
                    [
                        'display_name'   => $alias,
                        'normalized_key' => mb_strtolower($alias, 'UTF-8'),
                        'merged_to_id'   => $canonicalId,
                        'created_from'   => 'human',
                    ]
                );
            }
        }
    }
}