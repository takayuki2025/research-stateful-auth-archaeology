<?php

namespace Tests\Feature\AtlasKernel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Application\Support\AtlasIdempotency;

final class AnalysisRequestStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_to_running_to_done(): void
    {
        /** @var AnalysisRequestRepository $repo */
        $repo = app(AnalysisRequestRepository::class);

        $tenantId = 1;
        $itemId = 10;
        $version = 'v3.0.0';

        $payloadHash = AtlasIdempotency::payloadHash([
            'tenant_id' => $tenantId,
            'item_id' => $itemId,
            'raw_text' => 'Apple 美品 青',
            'analysis_version' => $version,
        ]);

        $key = AtlasIdempotency::idempotencyKey(
            $tenantId,
            $itemId,
            $version,
            $payloadHash
        );

        $req = $repo->reserveOrGet(
            $tenantId,
            $itemId,
            $version,
            $payloadHash,
            $key
        );

        $this->assertSame('pending', $req->status);

        $acquired = $repo->markRunning($req->id);
        $this->assertTrue($acquired);

        $repo->markDone($req->id);

        $row = \DB::table('analysis_requests')->where('id', $req->id)->first();
        $this->assertSame('done', $row->status);
        $this->assertNotNull($row->finished_at);
    }
}
