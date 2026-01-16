<?php

namespace App\Modules\Item\Presentation\Http\AtlasKernel\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use App\Modules\Item\Application\UseCase\AtlasKernel\DecideAtlasRequestUseCase;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideUseCase;

final class AtlasDecisionController extends Controller
{
    public function decide(
        int $analysisRequestId,
        Request $request,
        DecideUseCase $useCase
    ) {
        \Log::info('[DecideController] called', $request->all());

        $useCase->handle(
            $analysisRequestId,
            auth()->id(),
            'human',
            $request->all() // ★ v3 は validated 全体を渡す
        );
\Log::info('[DecideController] called', $request->all());
        return response()->json(['status' => 'ok']);
        return response()->json(['status' => 'ok']);
    }
}

