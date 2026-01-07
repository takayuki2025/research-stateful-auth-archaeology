<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Command\PublishItemUseCase;
use App\Modules\Item\Application\Dto\Item\PublishItemInput;
use App\Modules\Auth\Application\Service\AuthContext;

final class PublishItemController extends Controller
{
    public function __construct(
        private PublishItemUseCase $useCase,
        private AuthContext $authContext,
    ) {
    }

    public function __invoke(Request $request, string $draftId): JsonResponse
    {
        $principal = $this->authContext->principal();
        if (! $principal) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // ★ Publish では shop_id 必須（SHOP 出品の場合）
        $input = new PublishItemInput(
            draftId: $draftId,
            shopId: $request->input('shop_id'), // ← ここ
        );

        $this->useCase->execute($input, $principal, null);

        return response()->json(['status' => 'ok']);
    }
}
