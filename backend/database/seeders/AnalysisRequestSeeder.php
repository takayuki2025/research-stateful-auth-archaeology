<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AnalysisRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('analysis_requests')->insert([
            [
                'tenant_id'        => null,
                'item_id'          => 1, // ★ items.id が存在する値にする
                'analysis_version' => 'v3_ai',
                'payload_hash'     => hash('sha256', 'dummy-payload-1'),
                'idempotency_key'  => (string) Str::uuid(),
                'status'           => 'done',
                'started_at'       => now()->subMinutes(2),
                'finished_at'      => now(),
                'retry_count'      => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }
}