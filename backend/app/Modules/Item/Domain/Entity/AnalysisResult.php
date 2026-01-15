<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Entity;

use DateTimeImmutable;

final class AnalysisResult
{
    public function __construct(
        public readonly int $requestId,

        public readonly ?string $brandName,
        public readonly ?string $conditionName,
        public readonly ?string $colorName,
        public readonly ?array $classifiedTokens,

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
    ?string $brandName,
    ?string $conditionName,
    ?string $colorName,
    ?array $classifiedTokens,
    array $confidenceMap,
    ?float $overallConfidence,
    ?array $evidence,
    string $status,
    DateTimeImmutable $createdAt,
): self {
    return new self(
        requestId: $requestId,
        brandName: $brandName,
        conditionName: $conditionName,
        colorName: $colorName,
        classifiedTokens: $classifiedTokens,
        confidenceMap: $confidenceMap,
        overallConfidence: $overallConfidence,
        evidence: $evidence,
        status: $status,
        createdAt: $createdAt,
    );
    }

    public function toProvisionalDisplay(): array
{
    return [
        'brand' => [
            'name' => $this->brandName,
        ],
        'condition' => [
            'name' => $this->conditionName,
        ],
        'color' => [
            'name' => $this->colorName,
        ],

        // ✅ Review / Learning 用の証跡
        'tokens' => [
            'brand'     => $this->classifiedTokens['brand']     ?? [],
            'condition' => $this->classifiedTokens['condition'] ?? [],
            'color'     => $this->classifiedTokens['color']     ?? [],
        ],

        'confidence_map' => $this->confidenceMap,
        'overall_confidence' => $this->overallConfidence,
    ];
}
}
