<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Ledger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Ledger\GetLedgerEntriesUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetLedgerEntriesController extends Controller
{
    public function __construct(
        private GetLedgerEntriesUseCase $useCase,
    ) {
    }

    /**
     * GET /api/ledger/entries?shop_id=1&from=YYYY-MM-DD&to=YYYY-MM-DD&limit=50&cursor=...
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'shop_id' => 'required|integer',
            'from' => 'required|string',
            'to' => 'required|string',
            'limit' => 'nullable|integer',
            'cursor' => 'nullable|integer',
        ]);

        $out = $this->useCase->handle(
            shopId: (int)$request->input('shop_id'),
            from: (string)$request->input('from'),
            to: (string)$request->input('to'),
            limit: (int)($request->input('limit') ?? 50),
            cursor: $request->filled('cursor') ? (int)$request->input('cursor') : null,
        );

        return response()->json($out->toArray(), 200);
    }
}