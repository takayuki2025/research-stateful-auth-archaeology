<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ConditionEntity;
use App\Modules\Item\Domain\Repository\ConditionEntityRepository;

final class EloquentConditionEntityRepository implements ConditionEntityRepository
{
    public function resolveOrCreate(string $input): int
    {
        $input = trim($input);
        if ($input === '') {
            throw new \InvalidArgumentException('condition input is empty');
        }

        $entity = ConditionEntity::query()
            ->where('canonical_name', $input)
            ->first();

        if ($entity) {
            return (int)$entity->id;
        }

        $created = ConditionEntity::create([
            'canonical_name' => $input,
            'confidence'     => null,
            'created_from'   => 'human',
        ]);

        return (int)$created->id;
    }
}