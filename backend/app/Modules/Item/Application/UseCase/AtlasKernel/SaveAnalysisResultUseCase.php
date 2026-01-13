<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

final class SaveAnalysisResultUseCase
{
    public function __construct(
        private AnalysisResultRepository $results
    ) {}

    public function handle(
        int $analysisRequestId,
        int $itemId,
        array $analysisPayload
    ): void {
        // v3固定：request_id を payload に注入
        $payload = array_merge(
            $analysisPayload,
            ['request_id' => $analysisRequestId]
        );

        // 旧結果を superseded
        $this->results->supersedeByItem($itemId);

        // 新しい技術スナップショット保存
        $this->results->save($itemId, $payload);
    }
}