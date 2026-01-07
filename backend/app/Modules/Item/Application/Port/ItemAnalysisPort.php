<?php

namespace App\Modules\Item\Application\Port;

interface ItemAnalysisPort
{
    public function dispatchBrandNormalization(
        int $itemId,
        string $rawBrand,
        ?string $knownAssetsRef = 'brands_v1'
    ): void;

    public function dispatchExplainTermsExtraction(
        int $itemId,
        string $explain,
        ?string $knownAssetsRef = null
    ): void;
}
