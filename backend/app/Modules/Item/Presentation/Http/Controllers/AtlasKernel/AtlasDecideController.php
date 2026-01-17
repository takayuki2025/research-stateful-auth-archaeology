<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideUseCase;
use DomainException;

final class AtlasDecideController extends Controller
{
    public function __invoke(
        Request $request,
        string $shop_code,
        string $request_id,
        DecideUseCase $useCase
    ) {
        \Log::info('[AtlasDecideController] called', [
            'shop_code'  => $shop_code,
            'request_id' => $request_id,
            'payload'    => $request->all(),
        ]);

        try {
            $useCase->handle(
                (int) $request_id,
                auth()->id(),
                'human',
                $request->all()
            );

            return response()->json([
                'status' => 'ok',
            ]);
        } catch (DomainException $e) {
            // ★ ここが重要
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}