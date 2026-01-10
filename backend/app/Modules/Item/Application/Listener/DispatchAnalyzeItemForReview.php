<?php

namespace App\Modules\Item\Application\Listener;

use App\Modules\Item\Application\Event\ItemImported;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;

final class DispatchAnalyzeItemForReview
{
    public function handle(ItemImported $event): void
    {
        AnalyzeItemForReviewJob::dispatch(
            itemId: $event->itemId,
            rawText: $event->rawText,
            tenantId: $event->tenantId,
            source: $event->source,
        );
    }
}