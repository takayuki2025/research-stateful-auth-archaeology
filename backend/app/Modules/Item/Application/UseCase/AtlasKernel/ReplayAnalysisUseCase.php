<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Service\AtlasKernelService;

final class ReplayAnalysisUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
        private AtlasKernelService $atlas,
    ) {}

    public function handle(
        int $analysisRequestId,
        string $reason,
    ): void {
        $request = $this->requests->findOrFail($analysisRequestId);

        $this->results->supersedeByRequestId($analysisRequestId);

        $payload = $this->atlas->analyze(
            itemId: $request->itemId,
            rawText: $request->rawText,
        );

        $this->results->save(
            itemId: $request->itemId,
            payload: array_merge(
                $payload,
                ['request_id' => $analysisRequestId]
            )
        );
    }
}