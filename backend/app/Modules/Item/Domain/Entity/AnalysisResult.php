<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Entity;

use App\Models\AnalysisResult as EloquentAnalysisResult;
use DateTimeInterface;

final class AnalysisResult
{
    public function __construct(
        public readonly int $id,
        public readonly int $analysis_request_id,
        public readonly int $item_id,
        public readonly array $payload,
        public readonly ?array $tags,
        public readonly ?array $confidence,
        public readonly ?string $generated_version,
        public readonly ?string $raw_text,
        public readonly string $status,
        public readonly ?DateTimeInterface $created_at,
        public readonly ?DateTimeInterface $updated_at,
    ) {}

    public static function fromEloquent(EloquentAnalysisResult $row): self
    {
        return new self(
            id: (int)$row->id,
            analysis_request_id: (int)$row->analysis_request_id,
            item_id: (int)$row->item_id,
            payload: is_array($row->payload) ? $row->payload : (array)$row->payload,
            tags: is_array($row->tags) ? $row->tags : ($row->tags ? (array)$row->tags : null),
            confidence: is_array($row->confidence) ? $row->confidence : ($row->confidence ? (array)$row->confidence : null),
            generated_version: $row->generated_version,
            raw_text: $row->raw_text,
            status: (string)$row->status,
            created_at: $row->created_at,
            updated_at: $row->updated_at,
        );
    }
}