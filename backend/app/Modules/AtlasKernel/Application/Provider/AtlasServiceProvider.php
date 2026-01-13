<?php

namespace App\Modules\AtlasKernel\Application\Provider;

use Illuminate\Support\ServiceProvider;
use App\Modules\AtlasKernel\Domain\Analyzer\AtlasKernelAnalyzer;
use App\Modules\AtlasKernel\Application\Analyzer\CompositeAnalyzer;

final class AtlasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AtlasKernelAnalyzer::class,
            CompositeAnalyzer::class
        );
    }
}