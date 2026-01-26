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
     * v4.2: PDF text -> (conditional) OCR fallback
     * @return array{text:string, meta:array}
     */
    public function extractWithFallbackFromPdfBytes(string $pdfBytes, string $sourceUrl, ?string $language = 'ja'): array
    {
        // 1) pdf_text (fast path)
        $textRes = $this->callPdfText($pdfBytes, $sourceUrl, $language);
        $meta = $textRes['meta'] ?? [];
        $meta['pipeline'] = 'pdf_text_only';

        $text = $this->normalize((string)($textRes['text'] ?? ''));

        $ocrRecommended = (bool)($meta['ocr_recommended'] ?? false);

        // 2) conditional OCR
        if ($ocrRecommended) {
            $meta['pipeline'] = 'pdf_text_then_ocr';

            try {
                $ocrRes = $this->callPdfOcr($pdfBytes, $sourceUrl, $language);

                $ocrText = $this->normalize((string)($ocrRes['text'] ?? ''));

                // OCRが空ならフォールバック（監査は meta に残す）
                if ($ocrText !== '') {
                    $text = $ocrText;
                } else {
                    $meta['ocr_error'] = 'ocr returned empty text';
                }

                // OCRのmetaも合流（衝突はOCR側を優先）
                $ocrMeta = is_array($ocrRes['meta'] ?? null) ? $ocrRes['meta'] : [];
                $meta = array_merge($meta, $ocrMeta);

            } catch (\Throwable $e) {
                // ★ここが v4.2 安全弁（落とさない）
                $meta['ocr_error'] = $e->getMessage();
            }
        }

        return [
            'text' => $text,
            'meta' => is_array($meta) ? $meta : [],
        ];
    }

    private function callPdfText(string $pdfBytes, string $sourceUrl, ?string $language): array
    {
        $sha256 = hash('sha256', $pdfBytes);

        $res = Http::timeout(120)
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
            throw new \RuntimeException('pdf_text_extract failed: ' . $res->status() . ' ' . (string)$res->body());
        }

        $json = $res->json();
        return is_array($json) ? $json : [];
    }

    private function callPdfOcr(string $pdfBytes, string $sourceUrl, ?string $language): array
    {
        $sha256 = hash('sha256', $pdfBytes);

        $res = Http::timeout(180)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/') . '/v1/extract/pdf_ocr', [
                'content_b64' => base64_encode($pdfBytes),
                'content_sha256' => $sha256,
                'source_url' => $sourceUrl,
                'options' => [
                    'language' => $language,
                ],
            ]);

        if (!$res->ok()) {
            throw new \RuntimeException('pdf_ocr_extract failed: ' . $res->status() . ' ' . (string)$res->body());
        }

        $json = $res->json();
        return is_array($json) ? $json : [];
    }

    private function normalize(string $text): string
    {
        $t = str_replace(["\r\n", "\r"], "\n", $text);
        $t = preg_replace("/[ \t]+/u", " ", $t) ?? $t;
        $t = preg_replace("/\n{4,}/u", "\n\n\n", $t) ?? $t;
        return trim($t);
    }
}