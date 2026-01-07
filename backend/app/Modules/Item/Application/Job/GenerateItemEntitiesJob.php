<?php

namespace App\Modules\Item\Application\Job;

use App\Modules\Item\Domain\Service\AtlasKernelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class GenerateItemEntitiesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(
        private readonly int $itemId,
        private readonly string $rawText,
        private readonly ?int $tenantId,
    ) {
    }

    public function handle(AtlasKernelService $atlasKernel): void
    {

        logger()->info('[JOB] started', ['item_id' => $this->itemId]);

        $atlasKernel->analyze(
            itemId: $this->itemId,
            rawText: $this->rawText,
            tenantId: $this->tenantId,
        );

        logger()->info('[JOB] finished', ['item_id' => $this->itemId]);

    }

    public function failed(Throwable $e): void
    {
        logger()->error('[GenerateItemEntitiesJob failed]', [
            'item_id'  => $this->itemId,
            'tenantId' => $this->tenantId,
            'error'    => $e->getMessage(),
        ]);
    }
}
