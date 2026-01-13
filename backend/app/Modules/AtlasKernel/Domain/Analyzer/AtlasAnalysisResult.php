<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Domain\Analyzer;

final class AtlasAnalysisResult
{
    /**
     * @param array<string, array{
     *   value: mixed,
     *   confidence: float,
     *   confidence_version: string
     * }> $attributes
     */
    public function __construct(
        public readonly array $attributes
    ) {}

    public static function merge(array $results): self
    {
        $merged = [];

        foreach ($results as $result) {
            foreach ($result->attributes as $key => $payload) {
                // confidence が高いものを優先
                if (
                    !isset($merged[$key]) ||
                    $payload['confidence'] > $merged[$key]['confidence']
                ) {
                    $merged[$key] = $payload;
                }
            }
        }

        return new self($merged);
    }
}