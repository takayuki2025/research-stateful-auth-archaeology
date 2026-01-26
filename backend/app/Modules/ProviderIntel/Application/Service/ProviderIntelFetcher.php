<?php

namespace App\Modules\ProviderIntel\Application\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class ProviderIntelFetcher
{
    /**
     * @return array{body:string, content_type:string}
     */
    public function fetch(string $url): array
    {
        $verify = filter_var(env('PROVIDERINTEL_HTTP_VERIFY', 'true'), FILTER_VALIDATE_BOOL);

        $res = Http::timeout(60)
            ->withHeaders([
                'User-Agent' => 'ProviderIntelBot/1.0 (+TrustLedger; OmniCommerceCore)',
                'Accept'     => 'text/html,application/pdf,*/*',
            ])
            ->withOptions([
                // Guzzle option
                'allow_redirects' => true,
                'verify'          => $verify,
            ])
            ->get($url);

        if (!$res->ok()) {
            Log::error('[ProviderIntelFetcher] fetch failed', [
                'url' => $url,
                'status' => $res->status(),
                'content_type' => (string)($res->header('Content-Type') ?? ''),
                'body_head' => substr((string)$res->body(), 0, 200),
            ]);
            throw new \RuntimeException("fetch failed: {$res->status()}");
        }

        return [
            'body' => (string) $res->body(), // PDFもバイナリstringとして保持
            'content_type' => (string) ($res->header('Content-Type') ?? ''),
        ];
    }
}