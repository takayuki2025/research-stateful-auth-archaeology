<?php

namespace App\Modules\Review\Presentation\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Review\Application\UseCase\ConfirmReviewUseCase;
use App\Modules\Review\Application\UseCase\EditAndConfirmReviewUseCase;
use App\Modules\Review\Application\UseCase\RejectAndRetryReviewUseCase;

final class ReviewController extends Controller
{
    public function confirm(
        int $itemId,
        Request $request,
        ConfirmReviewUseCase $useCase
    ) {
        $useCase->handle(
            itemId: $itemId,
            decidedBy: $request->user()?->id,
            note: $request->input('note')
        );

        return response()->json(['status' => 'ok']);
    }

    public function editConfirm(
        int $itemId,
        Request $request,
        EditAndConfirmReviewUseCase $useCase
    ) {
        $validated = $request->validate([
            'tags' => ['required', 'array'],
        ]);

        $useCase->handle(
            itemId: $itemId,
            editedTags: $validated['tags'],
            decidedBy: $request->user()?->id,
            note: $request->input('note')
        );

        return response()->json(['status' => 'ok']);
    }

    public function reject(
        int $itemId,
        Request $request,
        RejectAndRetryReviewUseCase $useCase
    ) {
        $useCase->handle(
            itemId: $itemId,
            decidedBy: $request->user()?->id,
            note: $request->input('note')
        );

        return response()->json(['status' => 'ok']);
    }
}