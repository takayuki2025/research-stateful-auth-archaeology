<?php

namespace App\Modules\Item\Infrastructure\Port;

use App\Modules\Item\Application\Port\ItemAnalysisPort;
use App\Jobs\AnalyzeItemEntityWithAtlasKernel;
use App\Jobs\AnalyzeItemExplainTerms;

class QueueItemAnalysisAdapter implements ItemAnalysisPort
{
    public function dispatchBrandNormalization(
        int $itemId,
        string $rawBrand,
        ?string $knownAssetsRef = 'brands_v1'
    ): void {
        AnalyzeItemEntityWithAtlasKernel::dispatch(
            itemId: $itemId,
            entityType: 'brand',
            rawValue: $rawBrand,
            knownAssetsRef: $knownAssetsRef
        );
    }

    public function dispatchExplainTermsExtraction(
        int $itemId,
        string $explain,
        ?string $knownAssetsRef = null
    ): void {
        AnalyzeItemExplainTerms::dispatch(
            itemId: $itemId,
            explain: $explain,
            knownAssetsRef: $knownAssetsRef
        );
    }
}
