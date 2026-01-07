<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Application\UseCase\ConfirmOrderUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class ConfirmOrderController extends Controller
{
    public function __construct(
        private ConfirmOrderUseCase $useCase
    ) {
    }

    public function __invoke(Request $request, int $orderId): JsonResponse
    {

        \Log::info('[ConfirmOrderController] invoked', [
            'orderId' => $orderId,
            'userId' => $request->user()?->id,
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $this->useCase->handle(
            orderId: $orderId,
            userId: (int) $user->id
        );

        return response()->json(['status' => 'confirmed'], 200);
    }
}
