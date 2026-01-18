<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Ledger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\Dto\Ledger\ReplaySalePostingInput;
use App\Modules\Payment\Application\UseCase\Ledger\ReplaySalePostingUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReplaySalePostingController extends Controller
{
    public function __construct(
        private ReplaySalePostingUseCase $useCase,
    ) {
    }

    /**
     * POST /api/ledger/replay/sale
     * { "payment_id": 123 }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|integer',
        ]);

        try {
            $this->useCase->handle(new ReplaySalePostingInput(
                payment_id: (int)$request->input('payment_id')
            ));
            return response()->json(['ok' => true], 200);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}