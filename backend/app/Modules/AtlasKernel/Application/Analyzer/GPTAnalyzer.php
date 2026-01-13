<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Application\Analyzer;

use App\Modules\AtlasKernel\Domain\Analyzer\AtlasKernelAnalyzer;
use App\Modules\AtlasKernel\Domain\Analyzer\AtlasAnalysisResult;

final class GPTAnalyzer implements AtlasKernelAnalyzer
{
    public function analyze(int $analysisRequestId): AtlasAnalysisResult
    {
        // 🔥 API未接続：課金ゼロ
        return new AtlasAnalysisResult([
            'brand' => [
                'value' => 'Apple',
                'confidence' => 0.82,
                'confidence_version' => 'v3_gpt_dummy',
            ],
            'color' => [
                'value' => 'Red',
                'confidence' => 0.71,
                'confidence_version' => 'v3_gpt_dummy',
            ],
        ]);
    }
}