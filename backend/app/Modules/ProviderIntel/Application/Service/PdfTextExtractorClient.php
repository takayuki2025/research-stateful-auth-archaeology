<?php

namespace App\Modules\ProviderIntel\Application\Service;

use Illuminate\Support\Facades\Http;

final class PdfTextExtractorClient
{
    public function __construct(
        private ?string $baseUrl = null,
    ) {
        $this->baseUrl = $this->baseUrl ?: (string) env('ATLAS_KERNEL_EXTRACTOR_BASE_URL', '');
        if ($this->baseUrl === '') {
            throw new \RuntimeException('ATLAS_KERNEL_EXTRACTOR_BASE_URL is missing');
        }
    }

    /**
     * @return array{text:string, meta:array}
     */
    public function extractFromPdfBytes(string $pdfBytes, string $sourceUrl, ?string $language = 'ja'): array
    {
        $sha256 = hash('sha256', $pdfBytes);

        $res = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/') . '/v1/extract/pdf_text', [
                'content_b64' => base64_encode($pdfBytes),
                'content_sha256' => $sha256,
                'source_url' => $sourceUrl,
                'options' => [
                    'language' => $language,
                ],
            ]);

        if (!$res->ok()) {
            throw new \RuntimeException('pdf_text_extract failed: ' . $res->status() . ' ' . (string) $res->body());
        }

        $json = $res->json();
        $text = is_array($json) ? ($json['text'] ?? null) : null;
        $meta = is_array($json) ? ($json['meta'] ?? []) : [];

        if (!is_string($text)) {
            throw new \RuntimeException('pdf_text_extract invalid response: text missing');
        }

        return [
            'text' => $this->normalize($text),
            'meta' => is_array($meta) ? $meta : [],
        ];
    }

    private function normalize(string $text): string
    {
        // v4.1: 最小の正規化（後で強化可能）
        $t = str_replace(["\r\n", "\r"], "\n", $text);
        $t = preg_replace("/[ \t]+/u", " ", $t) ?? $t;
        $t = preg_replace("/\n{4,}/u", "\n\n\n", $t) ?? $t;
        return trim($t);
    }
}