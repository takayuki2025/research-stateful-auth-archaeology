<?php

namespace App\Modules\Item\Domain\Service;

use App\Models\BrandEntity;
use App\Models\ConditionEntity;
use App\Models\ColorEntity;

final class EntityFactory
{
    public function createCanonicalEntity(
        string $entityType,
        string $canonicalName,
        string $displayName,
        string $source = 'human'
    ): int {
        $normalized = $this->normalize($canonicalName);

        $data = [
            'normalized_key' => $normalized,
            'canonical_name' => $canonicalName,
            'display_name'   => $displayName,
            'is_primary'     => true,
            'confidence'     => 1.0,
            'created_from'   => $source,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        return match ($entityType) {
            'brand'     => BrandEntity::create($data)->id,
            'condition' => ConditionEntity::create($data)->id,
            'color'     => ColorEntity::create($data)->id,
            default     => throw new \InvalidArgumentException(
                "Unknown entity type: {$entityType}"
            ),
        };
    }

    private function normalize(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return mb_strtolower($s, 'UTF-8');
    }
}
