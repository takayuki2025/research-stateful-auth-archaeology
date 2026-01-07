<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Auth\Presentation\Http\Presenters\AuthUserPresenter;
use App\Modules\Auth\Application\UseCase\GetMyProfileUseCase;


final class MeController extends Controller
{
    public function __construct(
        private GetMyProfileUseCase $useCase
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json(
            $this->useCase->handle()->toArray()
        );
    }
}
