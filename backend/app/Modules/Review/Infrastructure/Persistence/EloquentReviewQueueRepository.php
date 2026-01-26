<?php

namespace App\Modules\Review\Infrastructure\Persistence;

use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use Illuminate\Support\Facades\DB;

final class EloquentReviewQueueRepository implements ReviewQueueRepository
{
    public function enqueue(?int $projectId, string $queueType, string $refType, int $refId, int $priority, ?array $summary): int
    {
        // 既に pending があるならそれを返す
        $existing = DB::table('review_queue_items')
            ->where('queue_type', $queueType)
            ->where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return (int)$existing->id;
        }

        return (int) DB::table('review_queue_items')->insertGetId([
            'project_id' => $projectId,
            'queue_type' => $queueType,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'status' => 'pending',
            'priority' => $priority,
            'summary_json' => $summary ? json_encode($summary, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function list(?string $queueType, ?string $status, int $limit, int $offset): array
    {
        $q = DB::table('review_queue_items')
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->limit($limit)
            ->offset($offset);

        if ($queueType) $q->where('queue_type', $queueType);
        if ($status) $q->where('status', $status);

        return $q->get()->map(fn($r) => [
            'id' => (int)$r->id,
            'project_id' => $r->project_id !== null ? (int)$r->project_id : null,
            'queue_type' => (string)$r->queue_type,
            'ref_type' => (string)$r->ref_type,
            'ref_id' => (int)$r->ref_id,
            'status' => (string)$r->status,
            'priority' => (int)$r->priority,
            'summary' => $r->summary_json ? json_decode($r->summary_json, true) : null,
            'decided_action' => $r->decided_action,
            'decided_by' => $r->decided_by !== null ? (int)$r->decided_by : null,
            'decided_at' => $r->decided_at,
            'note' => $r->note,
            'created_at' => $r->created_at,
            'updated_at' => $r->updated_at,
        ])->toArray();
    }

    public function get(int $id): ?array
    {
        $r = DB::table('review_queue_items')->where('id', $id)->first();
        if (!$r) return null;

        return [
            'id' => (int)$r->id,
            'project_id' => $r->project_id !== null ? (int)$r->project_id : null,
            'queue_type' => (string)$r->queue_type,
            'ref_type' => (string)$r->ref_type,
            'ref_id' => (int)$r->ref_id,
            'status' => (string)$r->status,
            'priority' => (int)$r->priority,
            'summary' => $r->summary_json ? json_decode($r->summary_json, true) : null,
            'decided_action' => $r->decided_action,
            'decided_by' => $r->decided_by !== null ? (int)$r->decided_by : null,
            'decided_at' => $r->decided_at,
            'note' => $r->note,
            'created_at' => $r->created_at,
            'updated_at' => $r->updated_at,
        ];
    }

    public function decide(int $id, string $action, ?int $decidedBy, ?string $note, ?array $extra): void
{
    $update = [
        'status' => 'decided',
        'decided_action' => $action,
        'decided_by' => $decidedBy,
        'decided_at' => now(),
        'note' => $note,
        'updated_at' => now(),
    ];

    if (is_array($extra) && !empty($extra)) {
        $update['summary_json'] = json_encode($extra, JSON_UNESCAPED_UNICODE);
    }

    DB::table('review_queue_items')->where('id', $id)->update($update);
}

public function updateStatus(int $id, string $status): void
{
    DB::table('review_queue_items')
        ->where('id', $id)
        ->update([
            'status' => $status,
            'updated_at' => now(),
        ]);
}

public function clearDecision(int $id): void
{
    DB::table('review_queue_items')
        ->where('id', $id)
        ->update([
            'decided_action' => null,
            'decided_by' => null,
            'decided_at' => null,
            'note' => null,
            'updated_at' => now(),
        ]);
}

public function closeInReviewForSameRef(string $queueType, string $refType, int $refId, int $excludeId): void
{
    DB::table('review_queue_items')
        ->where('queue_type', $queueType)
        ->where('ref_type', $refType)
        ->where('ref_id', $refId)
        ->where('status', 'in_review')
        ->where('id', '<>', $excludeId)
        ->update([
            // ✅ 「審査中」を閉じる（decidedに落とすのが最小）
            'status' => 'decided',
            'decided_action' => 'superseded',
            'decided_at' => now(),
            'updated_at' => now(),
        ]);
}
}