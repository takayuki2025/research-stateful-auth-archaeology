<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Application\UseCase\GetMyBoughtItemsUseCase;
use Illuminate\Http\JsonResponse;

final class MyPageBoughtController extends Controller
{
    public function __construct(
        private GetMyBoughtItemsUseCase $useCase,
    ) {
    }

    /**
     * GET /api/mypage/bought
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'items' => $this->useCase->handle(),
        ]);
    }
}
