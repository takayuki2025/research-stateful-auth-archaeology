<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controller\AtlasKernel;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\ListCanonicalEntitiesUseCase;

final class CanonicalEntityController extends Controller
{
    public function __construct(
        private ListCanonicalEntitiesUseCase $useCase
    ) {}

    public function index(string $type): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->handle($type),
        ]);
    }
}