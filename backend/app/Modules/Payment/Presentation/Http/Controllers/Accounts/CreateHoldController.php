<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Accounts\CreateHoldUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CreateHoldController extends Controller
{
    public function __construct(
        private CreateHoldUseCase $useCase,
    ) {}

    /**
     * POST /api/accounts/{accountId}/holds
     * { "amount": 1000, "currency": "JPY", "reason_code": "shipment_pending", "meta": {...}? }
     */
    public function __invoke(Request $request, int $accountId): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string',
            'reason_code' => 'required|string',
            'meta' => 'nullable|array',
        ]);

        try {
            $holdId = $this->useCase->handle(
                accountId: $accountId,
                amount: (int)$request->input('amount'),
                currency: (string)$request->input('currency'),
                reasonCode: (string)$request->input('reason_code'),
                meta: $request->input('meta'),
            );

            return response()->json(['ok' => true, 'hold_id' => $holdId], 200);

        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}