<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\Dto\StartPaymentInput;
use App\Modules\Payment\Application\UseCase\StartPaymentUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    public function __construct(
        private StartPaymentUseCase $startPayment
    ) {
    }

    /**
     * POST /api/payments/start
     * { "order_id": 123, "method": "card"|"konbini" }
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'method' => 'required|string|in:card,konbini',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $input = new StartPaymentInput(
                orderId: (int)$request->input('order_id'),
                method: (string)$request->input('method'),
            );

            $out = $this->startPayment->handle($input, (int)$user->id);

            return response()->json($out->toArray(), 200);

        } catch (\DomainException $e) {
            // ★ ビジネスルール違反
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            \Log::error('[Payment Start Failed]', [
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Payment start failed',
            ], 500);
        }
    }
}
