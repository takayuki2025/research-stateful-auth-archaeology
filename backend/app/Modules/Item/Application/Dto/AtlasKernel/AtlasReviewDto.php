<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class AtlasReviewDto
{
    public function __construct(
        public readonly int $requestId,
        public readonly string $status,

        // 🔹 追加①：Learning（人間入力）
        public readonly ?string $learning,

        // 🔹 追加②：分類済みトークン
        // [
        //   'brand' => string[],
        //   'condition' => string[],
        //   'color' => string[],
        // ]
        public readonly ?array $tokens,

        public readonly ?float $overallConfidence,
        public readonly array $before,
        public readonly array $after,
        public readonly array $diff,

        // v3固定：AFTER 側のみ
        public readonly array $confidenceMap,

        // UI表示補助
        public readonly array $attributes,

        public readonly array $beforeParsed,
    ) {}

    public function toArray(): array
    {
        return [
            'request_id'         => $this->requestId,
            'status'             => $this->status,

            // 🔹 新規（後方互換）
            'learning'           => $this->learning,
            'tokens'             => $this->tokens,

            'overall_confidence' => $this->overallConfidence,

            // v3固定
            'before'             => $this->before,
            'after'              => $this->after,
            'diff'               => $this->diff,

            // v3固定：confidenceはAFTER側のみ
            'confidence_map'     => $this->confidenceMap,

            // UI表示補助
            'attributes'         => $this->attributes,

            'beforeParsed'       => $this->beforeParsed,
        ];
    }
}