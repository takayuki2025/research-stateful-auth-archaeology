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
     * v4.2: PDF text -> (conditional) OCR fallback + decision gate
     *
     * @return array{text:string, meta:array}
     */
    public function extractWithFallbackFromPdfBytes(
        string $pdfBytes,
        string $sourceUrl,
        string $lang = 'jpn',
        string $engine = 'tesseract',   // 'tesseract'|'auto'
        string $mode = 'auto',          // 'auto'|'force_ocr'
        int $minLengthForNoOcr = 200,
        float $minConfidence = 70.0,
        int $budgetMaxMs = 3000,
        float $budgetMaxCostUsd = 0.0,
    ): array {
        $meta = [];
        $text = '';

        // -------------------------
        // 1) pdf_text（fast path）
        // -------------------------
        try {
            $textRes = $this->callPdfText($pdfBytes, $sourceUrl, $lang, $minLengthForNoOcr);
            $meta = is_array($textRes['meta'] ?? null) ? $textRes['meta'] : [];
            $text = $this->normalize((string)($textRes['text'] ?? ''));
        } catch (\Throwable $e) {
            // pdf_text が落ちたら監査に残し、後段OCRへ（落とさない）
            $meta['pdf_text_error'] = $e->getMessage();
            $text = '';
        }

        $ocrRecommended = (bool)($meta['ocr_recommended'] ?? false);
        $shouldOcr = ($mode === 'force_ocr') || $ocrRecommended;

        // pipelineは最後に確定する
        $pipeline = 'pdf_text_only';

        // -------------------------
        // 2) conditional OCR
        // -------------------------
        if ($shouldOcr) {
            $pipeline = ($mode === 'force_ocr') ? 'pdf_ocr_only' : 'pdf_text_then_ocr';

            try {
                $ocrRes = $this->callPdfOcr(
                    pdfBytes: $pdfBytes,
                    sourceUrl: $sourceUrl,
                    lang: $lang,
                    engine: $engine,
                    mode: $mode,
                    minConfidence: $minConfidence,
                    budgetMaxMs: $budgetMaxMs,
                    budgetMaxCostUsd: $budgetMaxCostUsd,
                );

                $ocrText = $this->normalize((string)($ocrRes['text'] ?? ''));

                if ($ocrText !== '') {
                    $text = $ocrText;
                } else {
                    $meta['ocr_error'] = 'ocr returned empty text';
                }

                $ocrMeta = is_array($ocrRes['meta'] ?? null) ? $ocrRes['meta'] : [];
                $meta = array_merge($meta, $ocrMeta);
            } catch (\Throwable $e) {
                $meta['ocr_error'] = $e->getMessage();
            }
        }

        // -------------------------
        // 3) 監査固定（必ず残す）
        // -------------------------
        $meta['pipeline'] = $pipeline;   // ★最重要：最後に確定
        $meta['lang'] = $lang;
        $meta['engine'] = $engine;
        $meta['mode'] = $mode;

        $meta['min_confidence'] = $minConfidence;
        $meta['budget'] = [
            'max_ms' => $budgetMaxMs,
            'max_cost_usd' => $budgetMaxCostUsd,
        ];

        // -------------------------
        // 4) decision gate（confidence × budget）
        // -------------------------
        $avgConf = null;
        if (isset($meta['avg_confidence']) && is_numeric($meta['avg_confidence'])) {
            $avgConf = (float)$meta['avg_confidence'];
        }

        $elapsedMs = null;
        if (isset($meta['elapsed_ms']) && is_numeric($meta['elapsed_ms'])) {
            $elapsedMs = (int)$meta['elapsed_ms'];
        }
        $meta['budget_used_ms'] = $elapsedMs;

        // budget exceeded -> review_required（監査最優先）
        if (is_int($elapsedMs) && $elapsedMs > $budgetMaxMs) {
            $meta['decision'] = 'review_required';
            $meta['decision_reason'] = 'budget_exceeded';
        } elseif ($avgConf === null) {
            $meta['decision'] = 'review_required';
            $meta['decision_reason'] = 'avg_confidence_missing';
        } elseif ($avgConf >= $minConfidence) {
            $meta['decision'] = 'accept';
            $meta['decision_reason'] = 'confidence_ok';
        } else {
            $meta['decision'] = 'review_required';
            $meta['decision_reason'] = 'confidence_below_threshold';
        }

        return [
            'text' => $text,
            'meta' => is_array($meta) ? $meta : [],
        ];
    }

    private function callPdfText(string $pdfBytes, string $sourceUrl, string $lang, int $minLengthForNoOcr): array
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
                    'lang' => $lang,
                    'min_length_for_no_ocr' => $minLengthForNoOcr,
                ],
            ]);

        if (!$res->ok()) {
            throw new \RuntimeException('pdf_text_extract failed: ' . $res->status() . ' ' . (string)$res->body());
        }

        $json = $res->json();
        return is_array($json) ? $json : [];
    }

    private function callPdfOcr(
        string $pdfBytes,
        string $sourceUrl,
        string $lang,
        string $engine,
        string $mode,
        float $minConfidence,
        int $budgetMaxMs,
        float $budgetMaxCostUsd,
    ): array {
        $sha256 = hash('sha256', $pdfBytes);

        $res = Http::timeout(180)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/') . '/v1/extract/pdf_ocr', [
                'content_b64' => base64_encode($pdfBytes),
                'content_sha256' => $sha256,
                'source_url' => $sourceUrl,
                'options' => [
                    'lang' => $lang,               // 'jpn' or 'jpn+eng'
                    'engine' => $engine,           // 'tesseract'|'auto'
                    'mode' => $mode,               // 'auto'|'force_ocr'
                    'min_confidence' => $minConfidence,
                    'budget' => [
                        'max_ms' => $budgetMaxMs,
                        'max_cost_usd' => $budgetMaxCostUsd,
                    ],
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