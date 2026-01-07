<?php

namespace App\Modules\Item\Application\Listener;

use App\Modules\Item\Domain\Event\ItemPublished;
use App\Modules\Item\Application\Job\GenerateItemEntitiesJob;
use App\Modules\Item\Domain\Service\AtlasKernelService;

final class GenerateItemEntitiesOnItemPublished
{
    public function __construct(
        private readonly AtlasKernelService $atlasKernel,
    ) {
    }

    public function handle(ItemPublished $event): void
    {
        // 🔁 同期実行（即時切り戻し用）
        if (config('atlas.mode') === 'sync') {
            $this->atlasKernel->analyze(
                itemId: $event->itemId,
                rawText: $event->rawText,
                tenantId: $event->tenantId,
            );
            return;
        }

        // 🚀 非同期（通常運用）
        GenerateItemEntitiesJob::dispatch(
            itemId: $event->itemId,
            rawText: $event->rawText,
            tenantId: $event->tenantId,
        );
    }
}
