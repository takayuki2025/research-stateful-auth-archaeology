<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\ReplayAnalysisUseCase;

final class ReplayAnalysisController extends Controller
{
    public function __construct(private ReplayAnalysisUseCase $replay) {}

    public function __invoke(int $id): JsonResponse
    {
        $this->replay->handle($id);

        return response()->json([
            'ok' => true,
            'message' => 'replayed',
        ]);
    }
}