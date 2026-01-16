<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;
use App\Modules\Item\Application\Dto\AtlasKernel\ResolveEntitiesInput;
use App\Modules\Item\Application\Dto\AtlasKernel\ResolveEntitiesOutput;

final class ResolveEntitiesUseCase
{
    public function __construct(
        private BrandEntityQueryRepository $brands,
        private ConditionEntityQueryRepository $conditions,
        private ColorEntityQueryRepository $colors,
    ) {}

    public function handle(ResolveEntitiesInput $input): ResolveEntitiesOutput
    {
        return DB::transaction(function () use ($input) {

            return new ResolveEntitiesOutput(
                brand_entity_id: $input->brand
                    ? $this->brands->resolveCanonicalByName(trim($input->brand))
                    : null,

                condition_entity_id: $input->condition
                    ? $this->conditions->resolveCanonicalByName(trim($input->condition))
                    : null,

                color_entity_id: $input->color
                    ? $this->colors->resolveCanonicalByName(trim($input->color))
                    : null,
            );
        });
    }
}