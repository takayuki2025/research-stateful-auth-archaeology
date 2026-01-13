<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use Illuminate\Http\JsonResponse;

final class AtlasRequestsController extends Controller
{
    public function __construct(
        private AnalysisRequestRepository $requests,
    ) {}

    public function index(string $shop_code): JsonResponse
    {
        $rows = $this->requests->listByShopCode($shop_code);

        return response()->json([
            'requests' => $rows,
        ]);
    }
}