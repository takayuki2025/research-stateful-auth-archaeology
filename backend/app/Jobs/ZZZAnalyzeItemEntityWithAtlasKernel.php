<?php

namespace App\Jobs;

use App\Models\ItemEntity;
use App\Models\ItemEntityAudit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class AnalyzeItemEntityWithAtlasKernel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $itemId,
        public string $entityType,
        public string $rawValue,
        public ?string $knownAssetsRef = null,
        public string $reason = 'manual_reanalyze',
        public array $context = []
    ) {
    }

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    public function handle()
{
    // v3 移行済み。旧 Job は無効化
    return;
}}