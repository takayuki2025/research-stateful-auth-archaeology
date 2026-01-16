<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Modules\Item\Application\UseCase\AtlasKernel\ListCanonicalEntitiesUseCase;
use Illuminate\Http\JsonResponse;

final class EntityController
{
    public function __construct(
        private ListCanonicalEntitiesUseCase $useCase,
    ) {}

    public function brands(): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->handle('brand'),
        ]);
    }

    public function conditions(): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->handle('condition'),
        ]);
    }

    public function colors(): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->handle('color'),
        ]);
    }
}