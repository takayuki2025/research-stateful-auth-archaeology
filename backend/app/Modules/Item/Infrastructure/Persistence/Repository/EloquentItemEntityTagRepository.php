<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\ItemEntityTagRepository;
use Illuminate\Support\Facades\DB;

final class EloquentItemEntityTagRepository implements ItemEntityTagRepository
{
    public function saveMany(
        int $itemEntityId,
        string $entityType,
        array $entities
    ): void {
        foreach ($entities as $entity) {
            DB::table('item_entity_tags')->insert([
                'item_entity_id' => $itemEntityId,
                'entity_type'    => $entityType,
                'entity_id'      => $entity['entity_id'] ?? null,
                'confidence'     => $entity['confidence'] ?? 0,
                'source'         => $entity['source'] ?? 'atlas_kernel',
                'meta'           => isset($entity['meta'])
                    ? json_encode($entity['meta'])
                    : null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function findByItemId(int $itemId): array
    {
        return DB::table('item_entity_tags as t')
            ->join('item_entities as e', 'e.id', '=', 't.item_entity_id')
            ->where('e.item_id', $itemId)
            ->where('e.is_latest', true)
            ->select(
                't.entity_type',
                't.entity_id',
                't.confidence',
                't.meta'
            )
            ->get()
            ->groupBy('entity_type')
            ->toArray();
    }
}
