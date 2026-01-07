<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ItemEntity;
use App\Jobs\AnalyzeItemEntityWithAtlasKernel;

class ReanalyzeByAssetsUpdate extends Command
{
    protected $signature = 'entity:reanalyze-by-assets 
        {entityType : brand / document_term / etc}
        {assetsRef : e.g. brands_v2, terms_mech_v2}';

    protected $description = 'Reanalyze latest entities after assets (dictionary) update';

    public function handle(): int
    {
        $entityType = $this->argument('entityType');
        $assetsRef  = $this->argument('assetsRef');

        $count = 0;

        ItemEntity::query()
            ->where('is_latest', true)
            ->where('entity_type', $entityType)
            ->each(function (ItemEntity $entity) use ($assetsRef, &$count) {
                AnalyzeItemEntityWithAtlasKernel::dispatch(
                    itemId: $entity->item_id,
                    entityType: $entity->entity_type,
                    rawValue: $entity->raw_value,
                    knownAssetsRef: $assetsRef,
                    reason: 'assets_updated'
                );
                $count++;
            });

        $this->info("Dispatched reanalysis jobs: {$count}");

        return self::SUCCESS;
    }
}
