<?php

namespace Tests\Feature\AtlasKernel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Modules\Item\Application\UseCase\Command\AtlasKernel\ReplayAnalysisUseCase;

final class ReplayAnalysisUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_replay_builds_provisional_entities_from_saved_payload(): void
    {
        // items の CHECK 制約に合わせる
        DB::table('items')->insert([
            'id' => 1,
            'item_origin'    => 'user_personal', // ← ここ重要
            'name'           => 'dummy item',
            'price'          => 1000,
            'price_currency' => 'JPY',
            'explain'        => 'dummy explain',
            'condition'      => 'dummy condition',
            'category'       => json_encode(['dummy']),
            'remain'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // analysis_requests: done を作る（あなたの実テーブルに合わせる）
        // ※ カラム名が異なるならあなたの migration に合わせて調整
        $requestId = DB::table('analysis_requests')->insertGetId([
            'tenant_id'        => 1,
            'item_id'          => 1,
            'analysis_version' => 'v3.0.0',
            'payload_hash'     => 'dummyhash',
            'idempotency_key'  => 'akv3:1:1:v3.0.0:dummyhash',
            'status'           => 'done',
            'retry_count'      => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // analysis_results: payload を作る（あなたの schema）
        $analysis = [
            'integration' => [
                'brand_identity' => [
                    'canonical' => 'Apple',
                    'confidence' => 0.9,
                ],
            ],
            'extraction' => [
                'condition' => ['美品'],
                'color' => ['青'],
            ],
            'normalization' => [
                'brand_entity_id' => null,
            ],
        ];

        DB::table('analysis_results')->insert([
            'item_id' => 1,
            'payload' => json_encode([
                'analysis' => $analysis,
                'status' => 'active',
                'analysis_version' => 'v3.0.0',
            ]),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // replay
        app(ReplayAnalysisUseCase::class)->handle($requestId);

        // assert: entity 1件 latest
        $this->assertSame(1, DB::table('item_entities')->where('item_id', 1)->where('is_latest', 1)->count());

        // assert: tags
        $this->assertSame(1, DB::table('item_entity_tags')->where('tag_type', 'brand')->count());
        $this->assertSame(1, DB::table('item_entity_tags')->where('tag_type', 'condition')->count());
        $this->assertSame(1, DB::table('item_entity_tags')->where('tag_type', 'color')->count());
    }
}