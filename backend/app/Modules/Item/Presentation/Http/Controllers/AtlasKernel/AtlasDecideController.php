<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideUseCase;
use App\Modules\Auth\Application\Context\AuthContext;
use App\Models\Shop as ShopModel;

final class AtlasDecideController extends Controller
{
    public function __construct(
        private DecideUseCase $useCase,
        private AuthContext $auth,
    ) {}

    public function __invoke(string $shop_code, int $request_id, Request $request): JsonResponse
    {
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();
        $this->authorize('review', $shopModel);

        $principal = $this->auth->principal();
        if (! $principal) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'decision_type'  => ['required', 'string', 'in:approve,edit_confirm,reject'],
            'note'           => ['nullable', 'string', 'max:2000'],
            'after_snapshot' => ['nullable', 'array'], // edit_confirm のときのみ使う
        ]);

        $this->useCase->handle(
            analysisRequestId: $request_id,
            decidedUserId: $principal->userId(),   // あなたの AuthPrincipal に合わせて
            decidedBy: 'human',
            input: $validated,
        );

        return response()->json(['status' => 'ok']);
    }
}