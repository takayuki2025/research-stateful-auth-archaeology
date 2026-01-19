<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Accounts\RecalculateBalanceUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecalculateBalanceController extends Controller
{
    public function __construct(
        private RecalculateBalanceUseCase $useCase,
    ) {}

    /**
     * POST /api/shops/{shopId}/balance/recalculate?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function __invoke(Request $request, int $shopId): JsonResponse
    {
        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
        ]);

        $accountId = $this->useCase->handle(
            shopId: $shopId,
            from: (string)$request->query('from'),
            to: (string)$request->query('to'),
            currency: 'JPY'
        );

        return response()->json(['ok' => true, 'account_id' => $accountId], 200);
    }
}