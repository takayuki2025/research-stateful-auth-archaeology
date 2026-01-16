<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Repository\BrandEntityRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityRepository;
use App\Modules\Item\Domain\Repository\ColorEntityRepository;
use App\Modules\Item\Application\Dto\AtlasKernel\ResolveEntitiesInput;
use App\Modules\Item\Application\Dto\AtlasKernel\ResolveEntitiesOutput;

final class ResolveEntitiesUseCase
{
    public function __construct(
        private BrandEntityRepository     $brands,
        private ConditionEntityRepository $conditions,
        private ColorEntityRepository     $colors,
    ) {}

    public function handle(ResolveEntitiesInput $input): ResolveEntitiesOutput
{
    return DB::transaction(function () use ($input) {

        return new ResolveEntitiesOutput(
            brand_entity_id: $input->brand
                ? $this->brands->resolveOrCreate(trim($input->brand))
                : null,

            condition_entity_id: $input->condition
                ? $this->conditions->resolveOrCreate(trim($input->condition))
                : null,

            color_entity_id: $input->color
                ? $this->colors->resolveOrCreate(trim($input->color))
                : null,
        );
    });
}
}