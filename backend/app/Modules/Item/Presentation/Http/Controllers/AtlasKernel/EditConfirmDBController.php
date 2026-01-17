<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

use App\Modules\Item\Application\UseCase\AtlasKernel\DecideUseCase;

final class EditConfirmDBController extends Controller
{
    /**
     * POST /api/shops/{shop_code}/atlas/requests/{request_id}/decide
     */
    public function decide(
        Request $request,
        DecideUseCase $useCase
    ): JsonResponse {
        $analysisRequestId = (int) $request->route('request_id');

        $useCase->handle(
            analysisRequestId: $analysisRequestId,
            decidedUserId: (int) Auth::id(),
            decidedByType: 'human',
            input: [
                'decision_type'   => $request->input('decision_type'),
                'resolvedEntities'=> $request->input('resolvedEntities'),
                'after_snapshot'  => $request->input('after_snapshot'),
                'beforeParsed'    => $request->input('beforeParsed'),
                'note'            => $request->input('note'),
            ]
        );

        return response()->json(['status' => 'ok']);
    }
}