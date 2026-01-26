<?php

namespace App\Modules\Review\Presentation\Http\Controllers\Admin\ReviewQueue;

use App\Http\Controllers\Controller;
use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use Illuminate\Http\Request;

final class DecideReviewQueueController extends Controller
{
    public function __construct(
        private ReviewQueueRepository $queue,
    ) {
    }

    public function __invoke(Request $request, int $id)
    {
        $data = $request->validate([
            'action' => 'required|string|in:approve,reject,request_more_info',
            'note' => 'nullable|string',
            'extra' => 'nullable|array',
        ]);

        // admin.fixed_or_key の場合は user_id が無い可能性があるので nullable でOK
        $decidedBy = $request->user()?->id;

        $this->queue->decide(
            $id,
            $data['action'],
            $decidedBy,
            $data['note'] ?? null,
            $data['extra'] ?? null
        );

        return response()->json(['ok' => true], 200);
    }
}
