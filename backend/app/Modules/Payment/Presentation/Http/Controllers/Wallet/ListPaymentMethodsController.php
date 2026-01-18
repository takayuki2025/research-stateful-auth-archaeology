<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Wallet\ListPaymentMethodsUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListPaymentMethodsController extends Controller
{
    public function __construct(
        private ListPaymentMethodsUseCase $useCase,
    ) {
    }

    /**
     * GET /api/wallet/payment-methods
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated_payment'], 401);
        }

        $dto = $this->useCase->handle((int)$user->id);

        return response()->json($dto->toArray(), 200);
    }
}