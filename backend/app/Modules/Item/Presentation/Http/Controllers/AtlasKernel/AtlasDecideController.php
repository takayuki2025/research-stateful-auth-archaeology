<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideUseCase;

final class AtlasDecideController extends Controller
{
    public function __invoke(
        Request $request,
        string $shop_code,          // ★ 先に shop_code
        string $request_id,         // ★ route param は string で受ける
        DecideUseCase $useCase
    ) {
        \Log::info('[AtlasDecideController] called', [
            'shop_code'  => $shop_code,
            'request_id' => $request_id,
            'payload'    => $request->all(),
        ]);

        $useCase->handle(
            (int)$request_id,        // ★ ここで int キャスト
            auth()->id(),
            'human',
            $request->all()
        );

        return response()->json(['status' => 'ok']);
    }
}