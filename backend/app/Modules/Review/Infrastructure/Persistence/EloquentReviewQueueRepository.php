<?php

namespace App\Modules\Review\Infrastructure\Persistence;

use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class EloquentReviewQueueRepository implements ReviewQueueRepository
{
    public function enqueue(?int $projectId, string $queueType, string $refType, int $refId, int $priority, ?array $summary): int
    {
        // 既に pending があるなら「最新summaryへ更新」して返す（v4運用の要）
        $existing = DB::table('review_queue_items')
            ->where('queue_type', $queueType)
            ->where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $update = [
                'updated_at' => now(),
            ];

            // priority は「より高い方」へ寄せる（運用で優先度が上がるケースに備える）
            $update['priority'] = max((int)$existing->priority, $priority);

            // project_id は null→値 への昇格のみ許可（既存の監査を壊さない）
            if ($existing->project_id === null && $projectId !== null) {
                $update['project_id'] = $projectId;
            }

            // ✅ ここが本命：summary_json を最新へ上書き
            if (is_array($summary)) {
                $update['summary_json'] = json_encode($summary, JSON_UNESCAPED_UNICODE);
            }

            DB::table('review_queue_items')
                ->where('id', (int)$existing->id)
                ->update($update);

            return (int)$existing->id;
        }

        return (int) DB::table('review_queue_items')->insertGetId([
            'project_id'   => $projectId,
            'queue_type'   => $queueType,
            'ref_type'     => $refType,
            'ref_id'       => $refId,
            'status'       => 'pending',
            'priority'     => $priority,
            'summary_json' => $summary ? json_encode($summary, JSON_UNESCAPED_UNICODE) : null,
            'created_at'   => now(),
            'updated_at'   => now(),
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

        // ✅ extra で summary_json を「丸ごと上書き」しない（diff_id等が消える事故を防止）
        if (is_array($extra) && !empty($extra)) {
            $current = DB::table('review_queue_items')->where('id', $id)->value('summary_json');
            $base = is_string($current) ? json_decode($current, true) : [];
            if (!is_array($base)) $base = [];

            $merged = array_merge($base, ['extra' => $extra]);
            $update['summary_json'] = json_encode($merged, JSON_UNESCAPED_UNICODE);
        }

        DB::table('review_queue_items')->where('id', $id)->update($update);
    }

    public function updateStatus(int $id, string $status): void
    {
        try {
            DB::table('review_queue_items')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        } catch (UniqueConstraintViolationException $e) {
            if ($status === 'in_review') {
                return;
            }
            throw $e;
        }
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
                'status' => 'archived',
                'decided_action' => 'superseded',
                'decided_at' => now(),
                'updated_at' => now(),
            ]);
    }
}