<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Entity;

use DateTimeImmutable;

final class AnalysisResult
{
    public function __construct(
        public readonly int $requestId,
        public readonly int $itemId,

        public readonly ?string $brandName,
        public readonly ?string $conditionName,
        public readonly ?string $colorName,

        public readonly array $confidenceMap,
        public readonly ?float $overallConfidence,

        public readonly ?array $evidence,
        public readonly string $status,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    /**
     * Repository 再構築専用（v3固定）
     */
    public static function reconstruct(
        int $requestId,
        int $itemId,
        ?string $brandName,
        ?string $conditionName,
        ?string $colorName,
        ?array $confidenceMap,
        ?float $overallConfidence,
        ?array $evidence,
        string $status,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            requestId: $requestId,
            itemId: $itemId,
            brandName: $brandName,
            conditionName: $conditionName,
            colorName: $colorName,
            confidenceMap: $confidenceMap ?? [],
            overallConfidence: $overallConfidence,
            evidence: $evidence,
            status: $status,
            createdAt: $createdAt,
        );
    }
}
