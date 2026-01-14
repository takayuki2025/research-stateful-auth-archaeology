<?php

namespace App\Modules\Item\Application\Listener;

use App\Modules\Item\Application\Event\ItemImported;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;
use Illuminate\Support\Facades\Bus;

final class AnalyzeImportedItemListener
{
    public function handle(ItemImported $event): void
    {
\Log::info('[🔥AnalyzeImportedItemListener] fired', [
            'itemId' => $event->itemId,
        ]);
        Bus::dispatch(new AnalyzeItemForReviewJob(
            itemId: $event->itemId,
            rawText: $event->rawText,
            tenantId: $event->tenantId,
            source: $event->source,
        ));
    }
}