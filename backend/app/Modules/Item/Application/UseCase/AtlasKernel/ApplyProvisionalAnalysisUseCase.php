<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

final class ApplyProvisionalAnalysisUseCase
{
    public function handle(int $itemId, array $analysis): void
    {
        // Aフェーズでは何もしない（Bフェーズで本実装）
    }
}