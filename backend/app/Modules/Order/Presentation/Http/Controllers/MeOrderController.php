<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Modules\Order\Application\UseCase\GetOrderHistoryUseCase;
use Illuminate\Http\Request;

final class MeOrderController
{
    public function index(
        GetOrderHistoryUseCase $useCase,
        Request $request
    ) {
        $user = $request->user();

        // auth.jwt ミドルウェア前提：user が必ず取れる想定
        return response()->json(
            $useCase->handle((int) $user->id)
        );
    }
}
