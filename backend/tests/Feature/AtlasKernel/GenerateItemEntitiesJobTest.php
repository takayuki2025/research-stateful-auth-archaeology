<?php

namespace Tests\Feature\AtlasKernel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

use App\Modules\Item\Application\Job\GenerateItemEntitiesJob;
use App\Modules\Item\Domain\Service\AtlasKernelPort;
use App\Modules\Item\Domain\Dto\AtlasAnalysisResult;
use App\Modules\Item\Application\UseCase\ApplyProvisionalAnalysisUseCase;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Enum\ItemOrigin;

final class GenerateItemEntitiesJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Repository bind（AnalysisRequest は既に必要）
        $this->app->bind(
            AnalysisRequestRepository::class,
            \App\Modules\Item\Infrastructure\Persistence\Repository\EloquentAnalysisRequestRepository::class
        );
    }

    public function test_job_is_idempotent(): void
{

    // ★ 最小限の items レコードを手動で作る（FK用）
   \DB::table('items')->insert([
    'id' => 1,

    // ★ CHECK 制約を満たす唯一の正解値
    'item_origin'    => 'user_personal',

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

    // --- AtlasKernelPort を mock ---
    $atlasMock = Mockery::mock(AtlasKernelPort::class);
    $atlasMock->shouldReceive('requestAnalysis')
        ->once()
         ->andReturn(new AtlasAnalysisResult(['dummy' => true]));

    $this->app->instance(AtlasKernelPort::class, $atlasMock);

        // --- Job 作成 ---
        $job = new GenerateItemEntitiesJob(
            itemId: 1,
            rawText: 'Apple 美品 青',
            tenantId: 1
        );

        // --- 同じ Job を2回実行 ---
        $job->handle(
            app(AtlasKernelPort::class),
            app(AnalysisResultRepository::class),
            app(ApplyProvisionalAnalysisUseCase::class),
            app(AnalysisRequestRepository::class),
        );

        $job->handle(
            app(AtlasKernelPort::class),
            app(AnalysisResultRepository::class),
            app(ApplyProvisionalAnalysisUseCase::class),
            app(AnalysisRequestRepository::class),
        );

        // --- 冪等性確認 ---
        $this->assertSame(
            1,
            \DB::table('analysis_requests')->count()
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}