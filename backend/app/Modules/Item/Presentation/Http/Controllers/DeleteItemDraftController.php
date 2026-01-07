<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Command\DeleteItemDraftUseCase;
use App\Modules\Auth\Application\Service\AuthContext;

final class DeleteItemDraftController extends Controller
{
    public function __construct(
        private DeleteItemDraftUseCase $useCase,
        private AuthContext $authContext,
    ) {
    }

    public function __invoke(string $draftId): JsonResponse
    {
        $principal = $this->authContext->principal();

        if (! $principal) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $this->useCase->execute($draftId, $principal);

        return response()->json(['status' => 'deleted']);
    }
}
