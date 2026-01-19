<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Accounts\ReleaseHoldUseCase;
use Illuminate\Http\JsonResponse;

final class ReleaseHoldController extends Controller
{
    public function __construct(
        private ReleaseHoldUseCase $useCase,
    ) {}

    /**
     * POST /api/holds/{holdId}/release
     */
    public function __invoke(int $holdId): JsonResponse
    {
        try {
            $this->useCase->handle($holdId);
            return response()->json(['ok' => true], 200);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
