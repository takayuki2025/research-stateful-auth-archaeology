<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;
use LogicException;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;

final class EloquentConditionEntityQueryRepository implements ConditionEntityQueryRepository
{
    public function resolveCanonicalByName(string $input): ?int
{
    $normalized = $this->normalize($input);

    $row = DB::table('condition_entities') // color_entities でも同様
        ->where('normalized_key', $normalized)
        ->first();

    if (!$row) {
        return null;
    }

    // canonical
    if ($row->is_primary) {
        return (int)$row->id;
    }

    // merged
    if ($row->merged_to_id) {
        return (int)$row->merged_to_id;
    }

    // 念のため
    return null;
}

    public function resolveCanonicalByEntityId(int $entityId): int
    {
        $row = DB::table('condition_entities')
            ->where('id', $entityId)
            ->first();

        if (!$row) {
            throw new LogicException("Condition entity not found: id={$entityId}");
        }

        if (!$row->is_primary) {
            throw new LogicException("Condition entity is not canonical: id={$entityId}");
        }

        return (int)$row->id;
    }

    private function normalize(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return mb_strtolower($s, 'UTF-8');
    }
}