<?php

namespace App\Modules\Item\Application\Listener;

use App\Modules\Item\Domain\Event\ItemPublished;
use App\Modules\Item\Application\Job\GenerateItemEntitiesJob;

final class GenerateItemEntitiesOnItemPublished
{
    public function handle(ItemPublished $event): void
    {
        GenerateItemEntitiesJob::dispatch(
            itemId: $event->itemId,
            rawText: $event->rawText,
            tenantId: $event->tenantId,
        );
    }
}