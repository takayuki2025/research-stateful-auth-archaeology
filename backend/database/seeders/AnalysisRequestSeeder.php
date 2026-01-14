<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalysisRequestSeeder extends Seeder
{
    public function run(): void
    {
        // ★ 既存の item_drafts から 1 件取得（SoT）
        $draftId = DB::table('item_drafts')->value('id');

        if (! $draftId) {
            throw new \RuntimeException('item_drafts is empty. Cannot seed analysis_requests.');
        }

        DB::table('analysis_requests')->insert([
            'tenant_id'        => null,
            'item_id'          => 1, // ItemsTableSeeder で作られている前提
            'analysis_version' => 'v3_ai',
            'raw_text' => 'seed dummy raw text',
            'payload_hash'     => hash('sha256', 'dummy-payload-1'),
            'idempotency_key'  => (string) Str::uuid(),
            'status'           => 'done',
            'started_at'       => now()->subMinutes(2),
            'finished_at'      => now(),
            'retry_count'      => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}