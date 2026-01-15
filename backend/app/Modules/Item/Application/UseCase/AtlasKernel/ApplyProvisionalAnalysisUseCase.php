<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

final class ApplyProvisionalAnalysisUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
    ) {}

    /**
     * v3 固定：
     * - requestId 主語
     * - Entity は作らない
     * - Repository には配列のみ渡す
     */
    public function handle(
        int $analysisRequestId,
        array $analysisRaw,
    ): void {
        DB::transaction(function () use ($analysisRequestId, $analysisRaw) {

            $request = $this->requests->findOrFail($analysisRequestId);

            $payload = [
                'analysis_request_id' => $analysisRequestId,
                'item_id'             => $request->itemId(),

                'brand_name'     => data_get($analysisRaw, 'brand.name'),
                'condition_name' => data_get($analysisRaw, 'condition.name'),
                'color_name'     => data_get($analysisRaw, 'color.name'),

                'classified_tokens' => data_get($analysisRaw, 'tokens'),
                'confidence_map'    => data_get($analysisRaw, 'confidence_map', []),
                'overall_confidence'=> data_get($analysisRaw, 'overall_confidence'),

                'evidence' => $analysisRaw,
                'source'   => 'ai_provisional',
                'status'   => 'provisional',
            ];

            $this->results->saveByRequestId(
                $analysisRequestId,
                $payload
            );
        });
    }
}