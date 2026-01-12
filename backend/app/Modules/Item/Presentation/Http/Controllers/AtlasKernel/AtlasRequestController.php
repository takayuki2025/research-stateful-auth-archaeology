<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Atlas\Application\Query\AnalysisRequestQuery;

final class AtlasRequestController extends Controller
{
    public function __construct(
        private AnalysisRequestQuery $requests
    ) {}

    public function index(string $shopCode)
    {
        return response()->json([
            'requests' => $this->requests->listByShopCode($shopCode),
        ]);
    }
}