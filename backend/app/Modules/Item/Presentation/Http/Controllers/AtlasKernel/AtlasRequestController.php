<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Application\Query\AtlasRequestListQuery;

final class AtlasRequestController extends Controller
{
    public function __construct(
        private AtlasRequestListQuery $query
    ) {}

    public function index(string $shop_code): JsonResponse
    {
        $requests = $this->query->listByShopCode($shop_code);

        return response()->json([
            'requests' => $requests,
        ]);
    }
}