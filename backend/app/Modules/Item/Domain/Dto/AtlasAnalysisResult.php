<?php

namespace App\Modules\Item\Domain\Dto;

final class AtlasAnalysisResult
{
    public function __construct(
        private array $analysis
    ) {}

    public function toArray(): array
    {
        return $this->analysis;
    }

    public function toProvisionalDisplay(): array
    {
        return [
            'brand' => [
                'name' =>
                    $this->analysis['integration']['brand_identity']['canonical']
                        ?? null,
                'confidence' =>
                    $this->analysis['integration']['brand_identity']['confidence']
                        ?? null,
            ],
            'condition' => [
                'name' =>
                    $this->analysis['extraction']['condition'][0]
                        ?? null,
                'confidence' => null,
            ],
            'color' => [
                'name' =>
                    $this->analysis['extraction']['color'][0]
                        ?? null,
                'confidence' => null,
            ],
            'confidence_map' => [
                'brand' =>
                    $this->analysis['integration']['brand_identity']['confidence']
                        ?? null,
            ],
            'overall_confidence' =>
                $this->analysis['integration']['brand_identity']['confidence']
                    ?? null,
        ];
    }
}