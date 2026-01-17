<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use LogicException;

final class EloquentBrandEntityQueryRepository implements BrandEntityQueryRepository
{
    public function resolveCanonicalByEntityId(int $entityId): int
    {
        $row = DB::table('brand_entities')->where('id', $entityId)->first();

        if (!$row) {
            throw new LogicException("Brand entity not found: id={$entityId}");
        }

        if ($row->is_primary) {
            return (int)$row->id;
        }

        if ($row->merged_to_id) {
            $canonical = DB::table('brand_entities')
                ->where('id', $row->merged_to_id)
                ->where('is_primary', true)
                ->first();

            if ($canonical) {
                return (int)$canonical->id;
            }
        }

        throw new LogicException("Canonical brand not found for id={$entityId}");
    }

    public function resolveCanonicalByName(string $input): ?int
    {
        $normalized = $this->normalize($input);

        $row = DB::table('brand_entities')
            ->where('normalized_key', $normalized)
            ->orWhereJsonContains('synonyms_json', $normalized)
            ->orderByDesc('is_primary')
            ->first();

        if (!$row) {
        // ★ 例外を投げてはいけない
        return null;
    }

        // primary
        if ($row->is_primary) {
            return (int)$row->id;
        }

        // merged
        if ($row->merged_to_id) {
            $canonical = DB::table('brand_entities')
                ->where('id', $row->merged_to_id)
                ->where('is_primary', true)
                ->first();

            if ($canonical) {
                return (int)$canonical->id;
            }
        }

        throw new LogicException("Canonical brand not found for name={$input}");
    }

    private function normalize(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return mb_strtolower($s, 'UTF-8');
    }

    public function listCanonicalOptions(): array
{
    return DB::table('brand_entities')
        ->where('is_primary', true)
        ->orderBy('canonical_name')
        ->get(['id', 'canonical_name'])
        ->map(fn ($r) => [
            'id' => (int) $r->id,
            'canonical_name' => (string) $r->canonical_name,
        ])
        ->toArray();
}
}
