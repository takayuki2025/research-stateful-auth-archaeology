<?php

namespace App\Modules\ProviderIntel\Application\Service;

use Illuminate\Support\Facades\Http;

final class ProviderIntelFetcher
{
    /**
     * Fetch raw content from source_url.
     * Returns: ['content_type' => string|null, 'body' => string]
     */
    public function fetch(string $url): array
    {
        // MVP: basic GET, follow redirects (Http client does)
        $res = Http::timeout(20)
            ->accept('*/*')
            ->get($url);

        if (!$res->successful()) {
            throw new \RuntimeException("Fetch failed: {$res->status()}");
        }

        return [
            'content_type' => $res->header('Content-Type'),
            'body' => (string)$res->body(),
        ];
    }
}
