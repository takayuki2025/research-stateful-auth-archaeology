<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Atlas\Application\Query\ReviewDecisionQuery;

final class AtlasDecisionHistoryController extends Controller
{
    public function __construct(
        private ReviewDecisionQuery $decisions
    ) {}

    public function history(string $shopCode, int $requestId)
    {
        return response()->json([
            'request_id' => $requestId,
            'decisions' => $this->decisions->listByRequestId($requestId),
        ]);
    }
}
