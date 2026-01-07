<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeItemExplainTerms implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $itemId,
        public string $explain,
        public ?string $knownAssetsRef = null // 例: terms_mech_v1
    ) {
    }

    public function handle(): void
    {
        $chunks = $this->splitExplain($this->explain);

        foreach ($chunks as $chunk) {
            // “document_term” として投入
            AnalyzeItemEntityWithAtlasKernel::dispatch(
                itemId: $this->itemId,
                entityType: 'document_term',
                rawValue: $chunk,
                knownAssetsRef: $this->knownAssetsRef
            );
        }
    }

    private function splitExplain(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        // 最小: 改行 + 句点で分割（必要なら強化）
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $parts = preg_split("/[。\n]+/u", $text) ?: [];

        // 短すぎるものは除外
        $parts = array_map('trim', $parts);
        $parts = array_values(array_filter($parts, fn ($p) => mb_strlen($p) >= 4));

        return $parts;
    }
}
