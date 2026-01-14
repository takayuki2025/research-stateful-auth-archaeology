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

    public function handle()
{
    $this->error('AnalyzeItemEntityWithAtlasKernel is deprecated.');
    return Command::SUCCESS;
}
}
