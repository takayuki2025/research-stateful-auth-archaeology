<?php

namespace App\Modules\Review\Infrastructure\Persistence;

use App\Modules\Review\Domain\Repository\ReviewRequestForInfoRepository;
use Illuminate\Support\Facades\DB;

final class EloquentReviewRequestForInfoRepository implements ReviewRequestForInfoRepository
{
    public function open(int $reviewQueueItemId, array $checklist, ?int $requestedBy): int
    {
        return (int) DB::table('review_requests_for_info')->insertGetId([
            'review_queue_item_id' => $reviewQueueItemId,
            'status' => 'open',
            'checklist_json' => json_encode($checklist, JSON_UNESCAPED_UNICODE),
            'requested_by' => $requestedBy,
            'requested_at' => now(),
            'closed_by' => null,
            'closed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function closeOpenByQueueItem(int $reviewQueueItemId, ?int $closedBy): void
    {
        DB::table('review_requests_for_info')
            ->where('review_queue_item_id', $reviewQueueItemId)
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_by' => $closedBy,
                'closed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function listByQueueItem(int $reviewQueueItemId): array
    {
        return DB::table('review_requests_for_info')
            ->where('review_queue_item_id', $reviewQueueItemId)
            ->orderByDesc('id')
            ->get()
            ->map(fn($r) => [
                'id' => (int)$r->id,
                'status' => (string)$r->status,
                'checklist' => json_decode($r->checklist_json, true),
                'requested_by' => $r->requested_by !== null ? (int)$r->requested_by : null,
                'requested_at' => $r->requested_at,
                'closed_by' => $r->closed_by !== null ? (int)$r->closed_by : null,
                'closed_at' => $r->closed_at,
                'created_at' => $r->created_at,
            ])->toArray();
    }
}