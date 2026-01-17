<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;

final class EditConfirmSelectController extends Controller
{
    public function brands(
        BrandEntityQueryRepository $brands
    ): JsonResponse {
        return response()->json(
            $brands->listCanonicalOptions()
        );
    }

    public function conditions(
        ConditionEntityQueryRepository $conditions
    ): JsonResponse {
        return response()->json(
            $conditions->listCanonicalOptions()
        );
    }

    public function colors(
        ColorEntityQueryRepository $colors
    ): JsonResponse {
        return response()->json(
            $colors->listCanonicalOptions()
        );
    }
}
