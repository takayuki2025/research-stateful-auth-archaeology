<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use InvalidArgumentException;
use App\Models\BrandEntity;
use App\Models\ConditionEntity;
use App\Models\ColorEntity;
use App\Modules\Item\Domain\Repository\CanonicalEntityRepository;

final class EloquentCanonicalEntityRepository
    implements CanonicalEntityRepository
{
    public function listByType(string $type): array
    {
        return match ($type) {
            'brand' => BrandEntity::query()
                ->orderBy('canonical_name')
                ->get(['id', 'canonical_name', 'display_name'])
                ->toArray(),

            'condition' => ConditionEntity::query()
                ->orderBy('canonical_name')
                ->get(['id', 'canonical_name', 'display_name'])
                ->toArray(),

            'color' => ColorEntity::query()
                ->orderBy('canonical_name')
                ->get(['id', 'canonical_name', 'display_name'])
                ->toArray(),

            default => throw new InvalidArgumentException(
                "Unknown canonical entity type: {$type}"
            ),
        };
    }
}