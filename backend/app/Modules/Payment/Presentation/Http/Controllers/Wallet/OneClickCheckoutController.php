<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Wallet\OneClickCheckoutUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OneClickCheckoutController extends Controller
{
    public function __construct(
        private OneClickCheckoutUseCase $useCase,
    ) {
    }

    /**
     * POST /api/wallet/one-click-checkout
     * { "order_id": 123, "payment_method_id": 5? }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'order_id' => 'required|integer',
            'payment_method_id' => 'nullable|integer',
        ]);

        try {
            $out = $this->useCase->handle(
                userId: (int)$user->id,
                orderId: (int)$request->input('order_id'),
                storedPaymentMethodId: $request->filled('payment_method_id')
                    ? (int)$request->input('payment_method_id')
                    : null
            );

            return response()->json($out->toArray(), 200);

        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::error('[OneClickCheckout Failed]', [
                'message' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'OneClick checkout failed'], 500);
        }
    }
}