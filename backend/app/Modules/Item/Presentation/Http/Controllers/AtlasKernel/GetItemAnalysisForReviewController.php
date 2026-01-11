<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Item\Application\UseCase\AtlasKernel\GetItemAnalysisForReviewUseCase;

final class GetItemAnalysisForReviewController extends Controller
{
    public function __invoke(
        int $itemId,
        GetItemAnalysisForReviewUseCase $useCase
    ): JsonResponse {
        return response()->json(
            $useCase->handle($itemId)
        );
    }
}