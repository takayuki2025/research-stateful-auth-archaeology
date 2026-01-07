<?php

namespace App\Modules\Comment\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Comment\Application\UseCase\Command\PostCommentUseCase;

final class PostCommentController extends Controller
{
    public function __invoke(Request $request, PostCommentUseCase $useCase)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $useCase->execute(
            userId: $request->user()->id,
            itemId: $validated['item_id'],
            body: $validated['comment']
        );

        return response()->json([
            'comment' => $comment,
        ], 201);
    }
}
