<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Service;

use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;
use Illuminate\Support\Facades\DB;

final class EntityFactory
{
    public function __construct(
        private BrandEntityQueryRepository     $brands,
        private ConditionEntityQueryRepository $conditions,
        private ColorEntityQueryRepository     $colors,
    ) {}

    /**
     * manual_override 専用
     * 既存があれば resolve、なければ canonical を新規作成
     */
    public function createCanonicalEntity(
        string $type,
        string $canonicalName,
        string $displayName,
        string $source
    ): int {
        return match ($type) {
            'brand'     => $this->createBrand($canonicalName, $displayName, $source),
            'condition' => $this->createCondition($canonicalName, $displayName, $source),
            'color'     => $this->createColor($canonicalName, $displayName, $source),
            default     => throw new \LogicException("Unknown entity type: {$type}"),
        };
    }

    private function createBrand(string $canonical, string $display, string $source): int
    {
        return (int) DB::table('brand_entities')->insertGetId([
            'canonical_name' => $canonical,
            'display_name'   => $display,
            'normalized_key' => mb_strtolower(trim($canonical), 'UTF-8'),
            'is_primary'     => true,
            'source'         => $source,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    private function createCondition(string $canonical, string $display, string $source): int
    {
        return (int) DB::table('condition_entities')->insertGetId([
            'canonical_name' => $canonical,
            'display_name'   => $display,
            'normalized_key' => mb_strtolower(trim($canonical), 'UTF-8'),
            'is_primary'     => true,
            'source'         => $source,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    private function createColor(string $canonical, string $display, string $source): int
    {
        return (int) DB::table('color_entities')->insertGetId([
            'canonical_name' => $canonical,
            'display_name'   => $display,
            'normalized_key' => mb_strtolower(trim($canonical), 'UTF-8'),
            'is_primary'     => true,
            'source'         => $source,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}