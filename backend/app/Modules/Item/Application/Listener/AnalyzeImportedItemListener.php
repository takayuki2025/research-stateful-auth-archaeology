<?php

namespace App\Modules\Item\Application\Listener;

use App\Modules\Item\Application\Event\ItemImported;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;
use App\Modules\Item\Application\UseCase\AtlasKernel\CreateAnalysisRequestUseCase;
use Illuminate\Support\Facades\Bus;

final class AnalyzeImportedItemListener
{
    public function __construct(
        private CreateAnalysisRequestUseCase $createRequest,
    ) {}

    public function handle(ItemImported $event): void
    {
        \Log::info('[🔥AnalyzeImportedItemListener] fired', [
            'itemId' => $event->itemId,
        ]);

        // v3 固定：まず request を生成して requestId を得る
        $requestId = $this->createRequest->handle(
            itemId: $event->itemId,
            itemDraftId: $event->itemDraftId, // publish経由で draftId を渡せるならここに渡す（後述）
            rawText: $event->rawText,
            tenantId: $event->tenantId,
            analysisVersion: 'v3_ai',
            triggeredByType: 'system',
            triggeredBy: null,
            triggerReason: 'item_imported:' . $event->source,
        );

        // v3 固定：Job は requestId のみ
        Bus::dispatch(new AnalyzeItemForReviewJob($requestId, 'initial'));
    }
}