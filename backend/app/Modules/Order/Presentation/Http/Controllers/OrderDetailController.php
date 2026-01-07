<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Application\UseCase\GetOrderDetailUseCase;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderDetailController extends Controller
{
    public function __construct(
        private GetOrderDetailUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $output = $this->useCase->handle(
                orderId: $orderId,
                userId: (int) $user->id,
            );

            return response()->json($output->toArray(), 200);

        } catch (DomainException $e) {

            // ★ メッセージで HTTP ステータスを切り替える
            return match ($e->getMessage()) {
                'Order not found' => response()->json(
                    ['message' => 'Order not found'],
                    404
                ),
                'Forbidden' => response()->json(
                    ['message' => 'Forbidden'],
                    403
                ),
                default => response()->json(
                    ['message' => 'Domain error'],
                    400
                ),
            };
        }
    }
}
