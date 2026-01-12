<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Shop as ShopModel;
use Illuminate\Http\JsonResponse;
use App\Modules\Item\Presentation\Http\Requests\AtlasKernel\DecideAnalysisRequestHttpRequest;
use App\Modules\Item\Application\Dto\AtlasKernel\DecideAnalysisRequestInput;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideAnalysisRequestUseCase;

final class AtlasRequestDecideController extends Controller
{
    public function __invoke(
        DecideAnalysisRequestHttpRequest $httpRequest,
        string $shop_code,
        int $request_id,
        DecideAnalysisRequestUseCase $useCase
    ): JsonResponse {
        // 認可（Aルート：ability名を揃える）
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();
        $this->authorize('review', $shopModel);

        $decision = (string)$httpRequest->input('decision');
        $reason   = $httpRequest->input('reason');

        $useCase->handle(new DecideAnalysisRequestInput(
            requestId: $request_id,
            decision: $decision,
            reason: is_string($reason) ? $reason : null,
            decidedUserId: (int)$httpRequest->user()->id,
            decidedBy: 'human'
        ));

        return response()->json([
            'ok' => true,
        ]);
    }
}