<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\GetAtlasReviewUseCase;
use Illuminate\Http\JsonResponse;

final class AtlasReviewController extends Controller
{
    public function __construct(
        private GetAtlasReviewUseCase $useCase
    ) {}

    public function show(string $shop_code, int $request_id): JsonResponse
    {
        $result = $this->useCase->handle(
            shopCode: $shop_code,
            analysisRequestId: $request_id,
        );

        return response()->json($result);
    }
}