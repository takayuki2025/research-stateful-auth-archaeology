<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\BrandEntity;
use App\Modules\Item\Domain\Repository\BrandEntityRepository;
use Illuminate\Support\Facades\DB;

final class EloquentBrandEntityRepository implements BrandEntityRepository
{
    public function resolveOrCreate(string $input): int
    {
        $normalized = $this->normalize($input);

        // ① 既存 canonical / alias を検索
        $existing = BrandEntity::where('normalized_key', $normalized)
            ->orderByDesc('is_primary')
            ->first();

        if ($existing) {
            return (int)$existing->id;
        }

        // ② なければ provisional（非 primary）として作成
        return BrandEntity::create([
            'normalized_key' => $normalized,
            'canonical_name' => $input,
            'display_name'   => $input,
            'is_primary'     => true,   // v3では最初は primary でOK
            'confidence'     => 1.0,
            'created_from'   => 'human',
        ])->id;
    }

    private function normalize(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return mb_strtolower($s, 'UTF-8');
    }
}