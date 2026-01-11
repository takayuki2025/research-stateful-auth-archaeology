<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Query\GetItemAnalysisResultUseCase;

final class GetItemAnalysisResultController extends Controller
{
    public function __invoke(
        int $itemId,
        GetItemAnalysisResultUseCase $useCase
    ): JsonResponse {
        $result = $useCase->handle($itemId);

        return response()->json($result);
    }
}