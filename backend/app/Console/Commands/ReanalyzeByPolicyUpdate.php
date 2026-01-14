<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ItemEntity;
use App\Jobs\AnalyzeItemEntityWithAtlasKernel;

class ReanalyzeByPolicyUpdate extends Command
{
    protected $signature = 'entity:reanalyze-by-policy {entityType}';
    protected $description = 'Reanalyze entities after policy update';

    public function handle()
{
    $this->error('AnalyzeItemEntityWithAtlasKernel is deprecated.');
    return Command::SUCCESS;
}}