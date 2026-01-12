<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\ReviewAnalysisRequestUseCase;

final class ReviewAnalysisRequestController extends Controller
{
    public function __construct(
        private ReviewAnalysisRequestUseCase $review
    ) {}

    public function __invoke(int $requestId, Request $request): JsonResponse
    {
        $this->review->handle(
            requestId: $requestId,
            action: $request->string('action'),
            selectedValue: $request->string('selected_value'),
            note: $request->string('note'),
            reviewerUserId: $request->user()->id
        );

        return response()->json(['ok' => true]);
    }
}