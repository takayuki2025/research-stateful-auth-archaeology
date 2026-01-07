<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Command\PublishItemDraftUseCase;
use App\Modules\Item\Application\Dto\Item\PublishItemDraftInput;
use App\Modules\Auth\Application\Service\AuthContext;

final class PublishItemDraftController extends Controller
{
    public function __construct(
        private PublishItemDraftUseCase $useCase,
        private AuthContext $authContext,
    ) {
    }

    public function __invoke(
        Request $request,
        string $draftId,
        PublishItemDraftUseCase $useCase,
        AuthContext $auth,
    ) {
        $validated = $request->validate([
            'item_origin' => ['required', 'in:USER_PERSONAL,SHOP_MANAGED'],
            'shop_id'     => ['nullable', 'integer'],
        ]);

        $input = new PublishItemDraftInput(
            draftId: $draftId,
            itemOrigin: $validated['item_origin'],
            shopId: $validated['shop_id'] ?? null,
        );

        $useCase->handle($input, $auth);

        return response()->json(['status' => 'ok']);
    }
}
