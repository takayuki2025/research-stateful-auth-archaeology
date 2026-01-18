<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Wallet\CreateSetupIntentUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CreateSetupIntentController extends Controller
{
    public function __construct(
        private CreateSetupIntentUseCase $useCase,
    ) {
    }

    /**
     * POST /api/wallet/setup-intent
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $out = $this->useCase->handle(
            userId: (int)$user->id,
            email: $user->email ?? null,
            name: $user->name ?? null,
        );

        return response()->json($out->toArray(), 200);
    }
}
