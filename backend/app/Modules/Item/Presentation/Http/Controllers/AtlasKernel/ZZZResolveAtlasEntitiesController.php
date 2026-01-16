<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controller\AtlasKernel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\ResolveEntitiesUseCase;
use App\Modules\Item\Application\Dto\AtlasKernel\ResolveEntitiesInput;

final class ResolveAtlasEntitiesController extends Controller
{
    public function __construct(
        private ResolveEntitiesUseCase $useCase,
    ) {}

    public function __invoke(
        Request $request,
        string $shop_code,
        int $request_id,
    ): JsonResponse {

        $snapshot = $request->input('snapshot', []);

        $input = new ResolveEntitiesInput(
            brand: $snapshot['brand']['value'] ?? null,
            condition: $snapshot['condition']['value'] ?? null,
            color: $snapshot['color']['value'] ?? null,
        );

        $resolved = $this->useCase->handle($input);

        return response()->json([
            'brand_entity_id'     => $resolved->brandEntityId,
            'condition_entity_id' => $resolved->conditionEntityId,
            'color_entity_id'     => $resolved->colorEntityId,
        ]);
    }
}