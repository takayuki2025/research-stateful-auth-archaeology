<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

interface BrandEntityRepository
{
    /**
     * v3 固定：
     * - manual_override / human 入力でのみ使用
     * - canonical_name 完全一致のみ許可
     */
    // public function resolveOrCreate(string $canonicalName): int;
}