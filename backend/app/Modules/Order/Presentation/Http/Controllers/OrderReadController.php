<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Application\UseCase\GetOrderDetailUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

final class OrderReadController extends Controller
{
    public function __construct(
        private GetOrderDetailUseCase $useCase,
    ) {
    }

    /**
     * GET /api/me/orders/{orderId}
     */
    public function show(Request $request, string $orderId): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $output = $this->useCase->handle(
                orderId: (int) $orderId,   // ★ ここでキャスト
                userId: (int) $user->id,
            );

\Log::info('[🔥DEBUG OrderRead]', [
    'user' => $request->user(),
    'auth_header' => $request->header('Authorization'),
]);

            return response()->json($output->toArray(), 200);

        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
