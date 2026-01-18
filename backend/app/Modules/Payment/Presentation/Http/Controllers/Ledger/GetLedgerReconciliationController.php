<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Ledger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Ledger\GetLedgerReconciliationUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetLedgerReconciliationController extends Controller
{
    public function __construct(
        private GetLedgerReconciliationUseCase $useCase,
    ) {
    }

    /**
     * GET /api/ledger/reconciliation?shop_id=1&from=YYYY-MM-DD&to=YYYY-MM-DD&limit=50
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'shop_id' => 'required|integer',
            'from' => 'required|string',
            'to' => 'required|string',
            'limit' => 'nullable|integer',
        ]);

        $out = $this->useCase->handle(
            shopId: (int)$request->input('shop_id'),
            from: (string)$request->input('from'),
            to: (string)$request->input('to'),
            limit: (int)($request->input('limit') ?? 50),
        );

        return response()->json($out->toArray(), 200);
    }
}