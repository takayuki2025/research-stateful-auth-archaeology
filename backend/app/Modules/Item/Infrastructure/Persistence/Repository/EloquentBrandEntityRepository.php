<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\BrandEntity;
use App\Modules\Item\Domain\Repository\BrandEntityRepository;

final class EloquentBrandEntityRepository implements BrandEntityRepository
{
    public function resolveOrCreate(string $input): int
    {
        $input = trim($input);
        if ($input === '') {
            throw new \InvalidArgumentException('brand input is empty');
        }

        // v3固定：canonical_name 完全一致
        $entity = BrandEntity::query()
            ->where('canonical_name', $input)
            ->first();

        if ($entity) {
            return (int)$entity->id;
        }

        $created = BrandEntity::create([
            'canonical_name' => $input,
            'display_name'   => $input,
            'synonyms_json'  => [],
            'confidence'     => 1.00, 
            'created_from'   => 'human', // v3固定
        ]);

        return (int)$created->id;
    }
}