<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Application\Dto\CreateOrderInput;
use App\Modules\Order\Application\UseCase\CreateOrderUseCase;
use App\Modules\Order\Application\UseCase\GetOrderDetailUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Order\Application\UseCase\ConfirmOrderAddressUseCase;

final class OrderController extends Controller
{
    public function __construct(
        private CreateOrderUseCase $createOrder,
        private GetOrderDetailUseCase $getOrderDetail,
        private ConfirmOrderAddressUseCase $confirmAddress,
    ) {
    }

    /**
     * POST /api/orders
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'shop_id' => 'required|integer',
            'items'   => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.name' => 'required|string',
            'items.*.price_amount' => 'required|integer|min:0',
            'items.*.price_currency' => 'required|string|max:10',
            'items.*.quantity' => 'nullable|integer|min:1',
            'meta' => 'nullable|array',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $input = new CreateOrderInput(
            shopId: (int) $request->input('shop_id'),
            userId: (int) $user->id,
            items: $request->input('items'),
            meta: $request->input('meta')
        );

        $out = $this->createOrder->handle($input);

        return response()->json($out->toArray(), 200);
    }

    /**
     * GET /api/orders/{orderId}
     */
    public function detail(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $out = $this->getOrderDetail->handle($orderId);

        if ((int) $out->userId !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($out->toArray(), 200);
    }

    /**
     * POST /api/orders/{orderId}/address
     */
    public function confirmAddress(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|integer',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $this->confirmAddress->handle(
            orderId: $orderId,
            userId: (int) $user->id,
            addressId: (int) $request->input('address_id')
        );

        return response()->json(['status' => 'ok'], 200);
    }
}
