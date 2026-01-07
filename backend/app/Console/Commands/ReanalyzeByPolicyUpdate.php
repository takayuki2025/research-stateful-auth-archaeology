<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ItemEntity;
use App\Jobs\AnalyzeItemEntityWithAtlasKernel;

class ReanalyzeByPolicyUpdate extends Command
{
    protected $signature = 'entity:reanalyze-by-policy {entityType}';
    protected $description = 'Reanalyze entities after policy update';

    public function handle(): int
    {
        $entityType = $this->argument('entityType');

        ItemEntity::query()
            ->where('is_latest', true)
            ->where('entity_type', $entityType)
            ->orderBy('id')
            ->chunkById(100, function ($entities) {
                foreach ($entities as $e) {
                    AnalyzeItemEntityWithAtlasKernel::dispatch(
                        itemId: $e->item_id,
                        entityType: $e->entity_type,
                        rawValue: $e->raw_value,
                        knownAssetsRef: null,
                        reason: 'policy_updated'
                    )->onQueue('reanalyze');
                }
            });

        $this->info("Reanalysis queued for {$entityType}");
        return self::SUCCESS;
    }
}
