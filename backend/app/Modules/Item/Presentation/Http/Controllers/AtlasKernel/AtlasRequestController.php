<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\Query\AtlasRequestListQuery;
use App\Modules\Item\Application\Assembler\AtlasKernel\AtlasRequestListAssembler;
use Illuminate\Http\JsonResponse;

final class AtlasRequestController extends Controller
{
    public function __construct(
        private AtlasRequestListQuery $query,
        private AtlasRequestListAssembler $assembler,
    ) {}

    public function index(string $shop_code): JsonResponse
    {
        // ① Query（Repository 由来の stdClass 行集合）
        $rows = $this->query->listByShopCode($shop_code);

        // ② Assembler（stdClass → UI 用配列 DTO）
        $requests = $this->assembler->assembleMany($rows);

        // ③ HTTP Response
        return response()->json([
            'requests' => $requests,
        ]);
    }
}