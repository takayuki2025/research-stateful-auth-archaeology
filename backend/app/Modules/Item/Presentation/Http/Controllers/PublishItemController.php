<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Command\PublishItemUseCase;
use App\Modules\Item\Application\Dto\Item\PublishItemInput;
use App\Modules\Auth\Application\Context\AuthContext;

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

        // ✅ v3固定: Publishは shop_id 必須（Shop出品）
        $validated = $request->validate([
            'shop_id' => ['required', 'integer', 'min:1'],
        ]);

        $input = new PublishItemInput(
            draftId: $draftId,
            shopId: (int) $validated['shop_id'],
        );

        // 第3引数 null は現状の設計通りでOK（tenantなどを後で入れるならここ）
        $this->useCase->execute($input, $principal, null);

        return response()->json(['status' => 'ok']);
    }
}