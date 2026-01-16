<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class ResolveEntitiesOutput
{
    public function __construct(
        public readonly ?int $brand_entity_id,
        public readonly ?int $condition_entity_id,
        public readonly ?int $color_entity_id,
    ) {}
}