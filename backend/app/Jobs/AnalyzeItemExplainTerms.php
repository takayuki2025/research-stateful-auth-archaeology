<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeItemExplainTerms implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $itemId,
        public string $explain,
        public ?string $knownAssetsRef = null // 例: terms_mech_v1
    ) {
    }

   public function handle()
{
    throw new \LogicException(
        'AnalyzeItemExplainTerms is deprecated. Use AtlasKernel v3 only.'
    );
}}