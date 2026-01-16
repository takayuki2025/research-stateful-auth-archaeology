<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class ResolveEntitiesInput
{
    public function __construct(
        public readonly ?string $brand,
        public readonly ?string $condition,
        public readonly ?string $color,
    ) {}
}
