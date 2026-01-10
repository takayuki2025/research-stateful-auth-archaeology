<?php

namespace App\Modules\Review\Infrastructure\External;

use Illuminate\Support\Facades\Http;

final class AtlasKernelClient
{
    public function requestItemAnalysis(int $itemId, array $options = []): void
    {
        $baseUrl = config('atlas.http_base_url'); // 例: http://atlaskernel:8080
        if (!$baseUrl) {
            // v3 MVP: 設定がないなら落とさずにログだけ
            \Log::warning('[AtlasKernelClient] http_base_url is not set. Skip request.', [
                'item_id' => $itemId,
                'options' => $options,
            ]);
            return;
        }

        Http::timeout(5)->post(rtrim($baseUrl, '/') . '/analysis/requests', [
            'subject_type' => 'item',
            'subject_id'   => $itemId,
            'options'      => $options,
        ]);
    }
}