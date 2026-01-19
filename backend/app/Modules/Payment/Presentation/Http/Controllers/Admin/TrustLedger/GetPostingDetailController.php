<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\GetPostingDetailUseCase;

final class GetPostingDetailController extends Controller
{
    public function __construct(
        private GetPostingDetailUseCase $useCase,
    ) {
    }

    public function __invoke(int $postingId)
    {
        $dto = $this->useCase->handle($postingId);
        return response()->json($dto->toArray(), 200);
    }
}