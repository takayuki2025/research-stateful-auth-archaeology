<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Item\Application\UseCase\AtlasKernel\ReplayAtlasRequestUseCase;
use App\Models\Shop as ShopModel;

final class AtlasReplayController extends Controller
{
    public function __construct(
        private ReplayAtlasRequestUseCase $useCase,
    ) {}

    public function __invoke(string $shop_code, int $request_id, Request $request): JsonResponse
    {
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();
        $this->authorize('review', $shopModel);

        $validated = $request->validate([
            'version' => ['required', 'string', 'max:64'],
            'reason'  => ['nullable', 'string', 'max:2000'],
        ]);

        $newId = $this->useCase->handle(
            analysisRequestId: $request_id,
            version: $validated['version'],
            reason: $validated['reason'] ?? null,
        );

        return response()->json([
            'status' => 'accepted',
            'new_request_id' => $newId,
        ]);
    }
}