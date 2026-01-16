<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\ConditionEntity;
use App\Modules\Item\Domain\Repository\ConditionEntityRepository;

final class EloquentConditionEntityRepository
    implements ConditionEntityRepository
{
    public function resolveOrCreate(string $input): int
    {
        $canonical = mb_strtolower(trim($input));

        $entity = ConditionEntity::query()
            ->where('canonical_name', $canonical)
            ->first();

        if ($entity) {
            return $entity->id;
        }

        return ConditionEntity::create([
            'canonical_name' => $canonical,
            'display_name'   => $input,
            'confidence'     => 1.0,
            'created_from'   => 'human', // v3固定（sourceはDomainで決める）
        ])->id;
    }
}