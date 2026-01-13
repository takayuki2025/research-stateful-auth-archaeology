<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideUseCase;
use App\Modules\Auth\Application\Context\AuthContext;

final class AtlasDecisionController extends Controller
{
    public function __construct(
        private DecideUseCase $useCase,
        private AuthContext $auth,
    ) {}

    public function decide(
        Request $request,
        string $shopCode,
        int $requestId,
    ): JsonResponse {
        $principal = $this->auth->principal();
        if (! $principal) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $this->useCase->handle(
            analysisRequestId: $requestId,
            decisionType: $request->string('decision_type'),
            decidedUserId: $principal->userId(),
            beforeSnapshot: $request->input('before_snapshot'),
            afterSnapshot: $request->input('after_snapshot'),
            note: $request->input('note'),
        );

        return response()->json(['status' => 'ok']);
    }
}