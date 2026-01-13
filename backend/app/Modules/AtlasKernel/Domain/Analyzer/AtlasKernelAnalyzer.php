<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Domain\Analyzer;

use App\Modules\AtlasKernel\Domain\Analyzer\AtlasAnalysisResult;

interface AtlasKernelAnalyzer
{
    public function analyze(int $analysisRequestId): AtlasAnalysisResult;
}