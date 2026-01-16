<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Shop as ShopModel;
use App\Modules\Auth\Application\Context\AuthContext;
use App\Modules\Item\Application\UseCase\AtlasKernel\ResolveEntitiesUseCase;
use App\Modules\Item\Application\Dto\AtlasKernel\ResolveEntitiesInput;


final class AtlasResolveController extends Controller
{
    public function __construct(
        private ResolveEntitiesUseCase $useCase,
        private AuthContext $auth,
    ) {}

    public function __invoke(
        string $shop_code,
        int $request_id,
        Request $request
    ): JsonResponse {
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();
        $this->authorize('review', $shopModel);

        if (! $this->auth->principal()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'brand'     => ['nullable', 'string'],
            'condition' => ['nullable', 'string'],
            'color'     => ['nullable', 'string'],
        ]);

        // ✅ DTO を生成して渡す
        $input = new ResolveEntitiesInput(
            brand: $validated['brand'] ?? null,
            condition: $validated['condition'] ?? null,
            color: $validated['color'] ?? null,
        );

        $out = $this->useCase->handle($input);

return response()->json($out);
    }}
