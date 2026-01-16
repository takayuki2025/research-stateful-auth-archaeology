<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


final class BrandEntitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('brand_entities')->updateOrInsert(
            ['canonical_name' => 'Apple'],
            [
                'display_name' => 'Apple',
                'normalized_key' => 'apple',
                'is_primary' => true,
                'created_from' => 'seed',
            ]
        );

        DB::table('brand_entities')->updateOrInsert(
            ['canonical_name' => 'あっぷる'],
            [
                'display_name' => 'あっぷる',
                'normalized_key' => 'あっぷる',
                'merged_to_id' => 1,
                'created_from' => 'human',
            ]
        );
    }
}