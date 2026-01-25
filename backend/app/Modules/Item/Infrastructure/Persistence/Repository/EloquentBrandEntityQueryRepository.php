<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use LogicException;

final class EloquentBrandEntityQueryRepository implements BrandEntityQueryRepository
{
    public function resolveCanonicalByEntityId(int $entityId): int
{
    $row = DB::table('brand_entities')->where('id', $entityId)->first(['id','is_primary','merged_to_id']);
    if (!$row) {
        throw new \DomainException("Brand entity not found: id={$entityId}");
    }

    if ((int)$row->is_primary === 1) {
        return (int)$row->id;
    }

    if (empty($row->merged_to_id)) {
        throw new \DomainException("Canonical brand not found for id={$entityId} (merged_to_id missing)");
    }

    $mergedId = (int)$row->merged_to_id;

    // 自己参照は壊れデータなので DomainException
    if ($mergedId === (int)$row->id) {
        throw new \DomainException("Canonical brand not found for id={$entityId} (self-merged)");
    }

    $canonical = DB::table('brand_entities')
        ->where('id', $mergedId)
        ->where('is_primary', true)
        ->first(['id']);

    if (!$canonical) {
        throw new \DomainException("Canonical brand not found for id={$entityId} (canonical missing)");
    }

    return (int)$canonical->id;
}

    public function resolveCanonicalByName(string $input): ?int
    {
        $name = trim($input);
        if ($name === '') return null;

        $normalized = $this->normalize($name);

        // 1) canonical_name 直一致（canonical行を優先）
        $row = DB::table('brand_entities')
            ->where('canonical_name', $name)
            ->orderByDesc('is_primary')
            ->first(['id','canonical_name','display_name','merged_to_id','is_primary']);

        if ($row) {
            // canonical行
            if ((int)$row->is_primary === 1 && (string)$row->canonical_name === (string)$row->display_name) {
                return (int)$row->id;
            }
            // alias行なら merged_to_id
            if (!empty($row->merged_to_id)) {
                return (int)$row->merged_to_id;
            }
            // 保険（壊れ）
            return (int)$row->id;
        }

        // 2) display_name 直一致（aliasを拾う）
        $row = DB::table('brand_entities')
            ->where('display_name', $name)
            ->orderByDesc('is_primary')
            ->first(['id','canonical_name','display_name','merged_to_id','is_primary']);

        if ($row) {
            if ((int)$row->is_primary === 1 && (string)$row->canonical_name === (string)$row->display_name) {
                return (int)$row->id;
            }
            if (!empty($row->merged_to_id)) {
                return (int)$row->merged_to_id;
            }
            // merged_to_idが無い場合は canonical_name から canonical行を引く
            $canon = DB::table('brand_entities')
                ->where('canonical_name', $row->canonical_name)
                ->whereColumn('canonical_name', 'display_name')
                ->where('is_primary', 1)
                ->value('id');
            return $canon ? (int)$canon : null;
        }

        // 3) normalized_key（Pythonのnormalizeと整合）
        $row = DB::table('brand_entities')
            ->where('normalized_key', $normalized)
            ->orderByDesc('is_primary')
            ->first(['id','canonical_name','display_name','merged_to_id','is_primary']);

        if ($row) {
            if ((int)$row->is_primary === 1 && (string)$row->canonical_name === (string)$row->display_name) {
                return (int)$row->id;
            }
            if (!empty($row->merged_to_id)) {
                return (int)$row->merged_to_id;
            }
            $canon = DB::table('brand_entities')
                ->where('canonical_name', $row->canonical_name)
                ->whereColumn('canonical_name', 'display_name')
                ->where('is_primary', 1)
                ->value('id');
            return $canon ? (int)$canon : null;
        }

        // 4) synonyms_json（canonical行に aliases配列を入れている前提）
        $row = DB::table('brand_entities')
            ->whereNotNull('synonyms_json')
            ->whereJsonContains('synonyms_json', $normalized)
            ->whereColumn('canonical_name', 'display_name') // canonical行のみ
            ->where('is_primary', 1)
            ->first(['id']);

        if ($row) {
            return (int)$row->id;
        }

        return null;
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
