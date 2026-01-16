<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ColorEntity;
use App\Modules\Item\Domain\Repository\ColorEntityRepository;

final class EloquentColorEntityRepository implements ColorEntityRepository
{
    public function resolveOrCreate(string $input): int
    {
        $input = trim($input);
        if ($input === '') {
            throw new \InvalidArgumentException('color input is empty');
        }

        $entity = ColorEntity::query()
            ->where('canonical_name', $input)
            ->first();

        if ($entity) {
            return (int)$entity->id;
        }

        $created = ColorEntity::create([
            'canonical_name' => $input,
            'confidence'     => 1.00, 
            'created_from'   => 'human',
        ]);

        return (int)$created->id;
    }
}