<?php

namespace App\Modules\Review\Application\Dto;

final class ReviewItemDetailDto
{
    public function __construct(
        public readonly int $itemId,
        public readonly array $itemRaw,
        public readonly array $aiProposal,
        public readonly array $diff,
        public readonly array $confidence,
        public readonly string $version,
        public readonly ?string $generatedAt,
        public readonly array $decisionHistory,
    ) {
    }

    public function toArray(): array
    {
        return [
            'item_id'          => $this->itemId,
            'item_raw'         => $this->itemRaw,
            'ai_proposal'      => $this->aiProposal,
            'diff'             => $this->diff,
            'confidence'       => $this->confidence,
            'version'          => $this->version,
            'generated_at'     => $this->generatedAt,
            'decision_history' => $this->decisionHistory,
        ];
    }
}