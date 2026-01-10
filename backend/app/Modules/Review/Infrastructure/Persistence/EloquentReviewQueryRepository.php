<?php

namespace App\Modules\Review\Infrastructure\Persistence;

use App\Modules\Review\Domain\Repository\ReviewQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentReviewQueryRepository implements ReviewQueryRepository
{
    public function getLatestAnalysis(int $itemId): array
    {
        // v3: latest item_entity + tags を「提案」として返す（analysis_results 未実装前提）
        $entity = DB::table('item_entities')
            ->where('item_id', $itemId)
            ->where('is_latest', true)
            ->first();

        if (!$entity) {
            return [
                'version' => 'v3',
                'confidence' => null,
                'tags' => [],
                'generated_at' => null,
            ];
        }

        $tags = DB::table('item_entity_tags')
            ->where('item_entity_id', $entity->id)
            ->select('tag_type', 'entity_id', 'display_name', 'confidence')
            ->get()
            ->groupBy('tag_type')
            ->map(fn($rows) => $rows->map(fn($r) => [
                'entity_id' => $r->entity_id ? (int)$r->entity_id : null,
                'display_name' => (string)$r->display_name,
                'confidence' => (float)$r->confidence,
            ])->toArray())
            ->toArray();

        return [
            'version' => (string)($entity->generated_version ?? 'v3'),
            'confidence' => $entity->confidence ? json_decode($entity->confidence, true) : null,
            'tags' => $tags,
            'generated_at' => $entity->generated_at,
        ];
    }

    public function listReviewItems(?string $status, ?float $confidenceMin, ?string $analyzedBy, int $limit = 50): array
    {
        // v3: status は簡易（decision の有無で判定）
        // 将来、review_status を別テーブル化しても良い
        $q = DB::table('items as i')
            ->leftJoin('item_entities as e', function ($join) {
                $join->on('e.item_id', '=', 'i.id')->where('e.is_latest', '=', 1);
            })
            ->leftJoin(DB::raw('(select subject_id, max(decided_at) as last_decided_at from review_decisions where subject_type="item" group by subject_id) d'), 'd.subject_id', '=', 'i.id')
            ->select(
                'i.id as item_id',
                DB::raw('case when d.last_decided_at is null then "pending" else "confirmed" end as status'),
                DB::raw('0 as diff_count'),
                DB::raw('"atlas" as analyzed_by'),
                'e.generated_at as analyzed_at'
            )
            ->orderByDesc('i.id')
            ->limit($limit);

        // confidenceMin / analyzedBy / status は v3 MVP では簡易（後で精緻化）
        return $q->get()->map(fn($r) => [
            'item_id' => (int)$r->item_id,
            'status' => (string)$r->status,
            'confidence_min' => 0.0,
            'diff_count' => (int)$r->diff_count,
            'analyzed_by' => (string)$r->analyzed_by,
            'analyzed_at' => $r->analyzed_at,
        ])->toArray();
    }

    public function getReviewItemDetail(int $itemId): array
    {
        $item = DB::table('items')->where('id', $itemId)->first();
        $analysis = $this->getLatestAnalysis($itemId);

        // v3 MVP: diff は UI 側で計算しても良いが、APIで簡易差分も返す
        $diff = [
            'brand' => null,
            'condition' => null,
            'color' => null,
        ];

        return [
            'item_raw' => $item ? (array)$item : [],
            'ai_proposal' => $analysis['tags'] ?? [],
            'diff' => $diff,
            'confidence' => $analysis['confidence'] ?? [],
            'version' => $analysis['version'] ?? 'v3',
            'generated_at' => $analysis['generated_at'] ?? null,
        ];
    }
}