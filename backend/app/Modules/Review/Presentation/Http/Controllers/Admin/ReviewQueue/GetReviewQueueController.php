<?php

namespace App\Modules\Review\Presentation\Http\Controllers\Admin\ReviewQueue;

use App\Http\Controllers\Controller;
use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use App\Modules\Review\Domain\Repository\ReviewRequestForInfoRepository;
use Illuminate\Http\Request;

final class GetReviewQueueController extends Controller
{
    public function __construct(
    private ReviewQueueRepository $queue,
    private ReviewRequestForInfoRepository $requestsForInfo,
) {}

public function __invoke(Request $request, int $id)
{
    $item = $this->queue->get($id);
    if (!$item) return response()->json(['message'=>'Not found'], 404);

    $item['requests_for_info'] = $this->requestsForInfo->listByQueueItem($id);
    $this->queue->clearDecision($id);
    $this->queue->updateStatus($id, 'in_review');

    return response()->json($item, 200);
}
}