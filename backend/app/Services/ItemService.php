<?php


use App\Jobs\AnalyzeEntityWithAtlasKernel;

class ItemService
{
    public function afterItemCreated(string $brandRaw)
    {
        AnalyzeEntityWithAtlasKernel::dispatch([
            'entity_type' => 'brand',
            'raw_value' => $brandRaw,
            'known_assets_ref' => 'brands_v1',
        ]);
    }
}
