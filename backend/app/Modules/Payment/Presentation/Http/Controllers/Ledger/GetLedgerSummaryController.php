<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Ledger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Ledger\GetLedgerSummaryUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetLedgerSummaryController extends Controller
{
    public function __construct(
        private GetLedgerSummaryUseCase $useCase,
    ) {
    }

    /**
     * GET /api/ledger/summary?shop_id=1&from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'shop_id' => 'required|integer',
            'from' => 'required|string',
            'to' => 'required|string',
        ]);

        $out = $this->useCase->handle(
            shopId: (int)$request->input('shop_id'),
            from: (string)$request->input('from'),
            to: (string)$request->input('to'),
        );

        return response()->json($out->toArray(), 200);
    }
}