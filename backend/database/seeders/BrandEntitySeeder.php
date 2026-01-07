<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandEntitySeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'canonical' => 'apple',
                'display'   => 'Apple',
            ],
            [
                'canonical' => 'ふじふぃるむ',
                'display'   => '富士フィルム',
            ],
        ];

        foreach ($brands as $brand) {
            DB::table('brand_entities')->updateOrInsert(
                ['canonical_name' => $brand['canonical']],
                [
                    'display_name' => $brand['display'],
                    'created_from' => 'manual',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }
    }
}
