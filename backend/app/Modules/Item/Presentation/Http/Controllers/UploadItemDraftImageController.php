<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Command\UploadItemDraftImageUseCase;
use App\Modules\Auth\Application\Context\AuthContext;

final class UploadItemDraftImageController extends Controller
{
    public function __construct(
        private UploadItemDraftImageUseCase $useCase,
        private AuthContext $authContext,
    ) {
    }

    public function __invoke(
        Request $request,
        string $draftId,
    ): JsonResponse {
        $principal = $this->authContext->principal();

        if (! $principal) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // ✅ 1. file validation
        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        // ✅ 2. file 保存（Presentation の責務）
        $file = $request->file('image');

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            'item_drafts',
            $filename,
            'public'
        );

        // ✅ 3. UseCase には「保存済み path」だけ渡す
        $this->useCase->execute(
            $draftId,
            $principal,
            $path,
        );

        return response()->json([
            'status' => 'ok',
            'path'   => $path,
        ]);
    }
}
