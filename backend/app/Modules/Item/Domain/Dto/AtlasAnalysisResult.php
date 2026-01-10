<?php

namespace App\Modules\Item\Domain\Dto;

final class AtlasAnalysisResult
{
    /**
     * @param array<string,array<int,array{
     *   display_name:string,
     *   entity_id:int|null,
     *   confidence:float
     * }>> $tags
     */
    public function __construct(
        public readonly array $tags,
        public readonly array $confidence,
        public readonly string $version,
        public readonly string $rawText,
    ) {}
}