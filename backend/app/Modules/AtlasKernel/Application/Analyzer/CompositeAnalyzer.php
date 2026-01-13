<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Application\Analyzer;

use App\Modules\AtlasKernel\Domain\Analyzer\AtlasKernelAnalyzer;
use App\Modules\AtlasKernel\Domain\Analyzer\AtlasAnalysisResult;

final class CompositeAnalyzer implements AtlasKernelAnalyzer
{
    public function __construct(
        private RuleBasedAnalyzer $rule,
        private GPTAnalyzer $gpt,
    ) {}

    public function analyze(int $analysisRequestId): AtlasAnalysisResult
    {
        $ruleResult = $this->rule->analyze($analysisRequestId);
        $gptResult  = $this->gpt->analyze($analysisRequestId);

        return AtlasAnalysisResult::merge([
            $ruleResult,
            $gptResult,
        ]);
    }
}
