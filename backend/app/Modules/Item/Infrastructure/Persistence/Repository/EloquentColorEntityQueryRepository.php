<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;
use LogicException;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;

final class EloquentColorEntityQueryRepository implements ColorEntityQueryRepository
{
    public function resolveCanonicalByName(string $input): ?int
{
    $normalized = $this->normalize($input);

    $row = DB::table('color_entities')
        ->where('normalized_key', $normalized)
        ->orWhere('canonical_name', $input)
        ->orWhere('display_name', $input)
        ->first();

    if (!$row) {
        return null;
    }

    if ((int)$row->is_primary === 1) {
        return (int)$row->id;
    }

    if ($row->merged_to_id !== null) {
        return (int)$row->merged_to_id;
    }

    return null;
}

    public function resolveCanonicalByEntityId(int $entityId): int
    {
        $row = DB::table('color_entities')
            ->where('id', $entityId)
            ->first();

        if (!$row) {
            throw new LogicException("Color entity not found: id={$entityId}");
        }

        if (!$row->is_primary) {
            throw new LogicException("Color entity is not canonical: id={$entityId}");
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