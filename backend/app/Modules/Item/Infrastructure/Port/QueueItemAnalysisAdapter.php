<?php

namespace App\Modules\Item\Infrastructure\Port;

use App\Modules\Item\Application\Port\ItemAnalysisPort;
use App\Jobs\AnalyzeItemEntityWithAtlasKernel;
use App\Jobs\AnalyzeItemExplainTerms;

final class QueueItemAnalysisAdapter implements ItemAnalysisPort
{
    public function dispatchBrandNormalization(
        int $itemId,
        string $rawBrand,
        ?string $knownAssetsRef = 'brands_v1'
    ): void {
        throw new \LogicException(
            'QueueItemAnalysisAdapter is deprecated. Use AtlasKernel v3 pipeline.'
        );
    }

    public function dispatchExplainTermsExtraction(
        int $itemId,
        string $explain,
        ?string $knownAssetsRef = null
    ): void {
        throw new \LogicException(
            'QueueItemAnalysisAdapter is deprecated. Use AtlasKernel v3 pipeline.'
        );
    }
}
