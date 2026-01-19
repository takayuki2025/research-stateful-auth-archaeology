<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Accounts\RequestPayoutUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RequestPayoutController extends Controller
{
    public function __construct(
        private RequestPayoutUseCase $useCase,
    ) {}

    /**
     * POST /api/accounts/{accountId}/payouts
     * { "amount": 1000, "currency": "JPY", "rail": "stripe", "meta": {...}? }
     */
    public function __invoke(Request $request, int $accountId): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string',
            'rail' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        try {
            $payoutId = $this->useCase->handle(
                accountId: $accountId,
                amount: (int)$request->input('amount'),
                currency: (string)$request->input('currency'),
                rail: (string)($request->input('rail') ?? 'stripe'),
                meta: $request->input('meta'),
            );

            return response()->json(['ok' => true, 'payout_id' => $payoutId], 200);

        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}