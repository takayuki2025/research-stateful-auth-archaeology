<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;


class ConditionEntitySeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            '新品',
            '未使用',
            '美品',
            '良好',
            '使用感あり',
        ];

        foreach ($conditions as $name) {
            DB::table('condition_entities')->updateOrInsert(
                ['canonical_name' => $name],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
