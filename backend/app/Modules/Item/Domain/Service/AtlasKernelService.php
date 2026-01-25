<?php

namespace App\Modules\Item\Domain\Service;

use App\Modules\Item\Infrastructure\External\HttpAtlasKernelClient;

final class AtlasKernelService
{
    public function __construct(
        private LocalDictionaryAnalyzer $local,
        private HttpAtlasKernelClient $http,
    ) {}

    public function requestAnalysis(
    int $itemId,
    string $rawText,
    ?int $tenantId = null,
    array $context = []   // ✅ 追加
): array {
    $mode = env('ATLAS_MODE', 'local');

    if ($mode === 'http') {
        return $this->http->analyze($itemId, $rawText, $tenantId, $context);
    }

    if ($mode === 'hybrid') {
        try {
            return $this->http->analyze($itemId, $rawText, $tenantId, $context);
        } catch (\Throwable $e) {
            logger()->warning('[AtlasKernel] http failed, fallback to local', [
                'error' => $e->getMessage(),
            ]);
            return $this->local->analyze($itemId, $rawText, $tenantId);
        }
    }

    return $this->local->analyze($itemId, $rawText, $tenantId);
}
}