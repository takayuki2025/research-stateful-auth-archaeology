<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class AtlasReviewDto
{
    public function __construct(
        public readonly int $requestId,
        public readonly string $status,
        public readonly ?float $overallConfidence,
        public readonly array $before,
        public readonly array $after,
        public readonly array $diff,
        public readonly array $confidenceMap, // ✅ 追加：UI固定仕様
        public readonly array $attributes,
    ) {}

    public function toArray(): array
    {
        return [
            'request_id'         => $this->requestId,
            'status'             => $this->status,
            'overall_confidence' => $this->overallConfidence,

            // v3固定
            'before'             => $this->before,
            'after'              => $this->after,
            'diff'               => $this->diff,

            // v3固定：confidenceはAFTER側のみ
            'confidence_map'     => $this->confidenceMap,

            // UI表示補助
            'attributes'         => $this->attributes,
        ];
    }
}