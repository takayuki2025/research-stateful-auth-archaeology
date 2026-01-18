<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Wallet\DetachPaymentMethodUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DetachPaymentMethodController extends Controller
{
    public function __construct(
        private DetachPaymentMethodUseCase $useCase,
    ) {
    }

    /**
     * DELETE /api/wallet/payment-methods/{id}
     */
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $this->useCase->handle((int)$user->id, (int)$id);
            return response()->json(['ok' => true], 200);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}