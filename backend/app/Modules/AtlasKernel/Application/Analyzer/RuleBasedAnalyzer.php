<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Application\Analyzer;

use App\Modules\AtlasKernel\Domain\Analyzer\AtlasKernelAnalyzer;
use App\Modules\AtlasKernel\Domain\Analyzer\AtlasAnalysisResult;

final class RuleBasedAnalyzer implements AtlasKernelAnalyzer
{
    public function analyze(int $analysisRequestId): AtlasAnalysisResult
    {
        // 例：正規表現・辞書・過去確定値など
        return new AtlasAnalysisResult([
            'brand' => [
                'value' => 'Apple',
                'confidence' => 0.95,
                'confidence_version' => 'v3_rule',
            ],
        ]);
    }
}