<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Accounts\MarkPayoutStatusUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MarkPayoutStatusController extends Controller
{
    public function __construct(
        private MarkPayoutStatusUseCase $useCase,
    ) {}

    /**
     * POST /api/payouts/{payoutId}/status
     * { "status": "processing|paid|failed", "provider_payout_id": "...?" }
     */
    public function __invoke(Request $request, int $payoutId): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:processing,paid,failed',
            'provider_payout_id' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        try {
            $this->useCase->handle(
                payoutId: $payoutId,
                status: (string)$request->input('status'),
                providerPayoutId: $request->input('provider_payout_id'),
                meta: $request->input('meta'),
            );

            return response()->json(['ok' => true], 200);

        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}