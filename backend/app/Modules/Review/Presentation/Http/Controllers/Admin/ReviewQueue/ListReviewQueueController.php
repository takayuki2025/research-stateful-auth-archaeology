<?php

namespace App\Modules\Review\Presentation\Http\Controllers\Admin\ReviewQueue;

use App\Http\Controllers\Controller;
use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use Illuminate\Http\Request;

final class ListReviewQueueController extends Controller
{
    public function __construct(
        private ReviewQueueRepository $queue,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'queue_type' => 'sometimes|string',
            'status' => 'sometimes|string|in:pending,in_review,decided,archived',
            'limit' => 'sometimes|integer|min:1|max:200',
            'offset' => 'sometimes|integer|min:0|max:100000',
        ]);

        $items = $this->queue->list(
            $data['queue_type'] ?? null,
            $data['status'] ?? null,
            (int)($data['limit'] ?? 50),
            (int)($data['offset'] ?? 0),
        );

        return response()->json(['items' => $items], 200);
    }
}