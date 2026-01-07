<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;



class ColorEntitySeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            '赤',
            '青',
            '黒',
            '白',
            'シルバー',
        ];

        foreach ($colors as $name) {
            DB::table('color_entities')->updateOrInsert(
                ['canonical_name' => $name],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
