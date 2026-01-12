<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use DomainException;
use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

final class ReplayAnalysisUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
        private BuildProvisionalItemEntitiesUseCase $builder,
    ) {}

    public function handle(int $analysisRequestId): void
    {
        $req = $this->requests->getById($analysisRequestId);

        if (!$req) {
            throw new DomainException('analysis_request not found');
        }

        if ($req->status !== 'done') {
            throw new DomainException('analysis_request is not done');
        }

        $result = $this->results->getLatestActiveByItemId($req->item_id);

        if (!$result) {
            throw new DomainException('analysis_result not found');
        }

        $payload = is_string($result->payload)
            ? json_decode($result->payload, true)
            : $result->payload;

        $analysis = $payload['analysis'] ?? null;

        if (!is_array($analysis)) {
            throw new DomainException('invalid analysis payload');
        }

        DB::transaction(function () use ($req, $analysis) {
            // 再適用
            $this->builder->handle($req->item_id, $analysis);

            // イベント記録（Bフェーズの本質）
            DB::table('analysis_request_events')->insert([
                'analysis_request_id' => $req->id,
                'event_type'          => 'replayed',
                'event_payload'       => null,
                'created_at'          => now(),
            ]);
        });
    }
}