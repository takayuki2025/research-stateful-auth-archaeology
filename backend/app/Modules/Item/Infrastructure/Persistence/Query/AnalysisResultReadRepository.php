<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class AnalysisResultReadRepository
{
    /**
     * 商品詳細表示用（Item Detail）
     * 最新の active / provisional な解析結果を返す
     *
     * @return array<string,mixed>|null
     */
    public function findLatestActiveByItemId(int $itemId): ?array
{
    // ✅ 最新 request
    $latestReq = DB::table('analysis_requests')
        ->where('item_id', $itemId)
        ->orderByDesc('id')
        ->first(['id']);

    if (! $latestReq) {
        return null;
    }

    $requestId = (int)$latestReq->id;

    // ✅ 1) 最新requestの active/provisional があれば AI display
    $row = DB::table('analysis_results')
        ->where('analysis_request_id', $requestId)
        ->whereIn('status', ['active', 'provisional'])
        ->orderByDesc('id')
        ->first();

    if ($row) {
        return $this->mapRow($row);
    }

    // ✅ 2) 無い場合：最新 decision が reject なら after_snapshot を raw display に使う
    $dec = DB::table('review_decisions')
        ->where('analysis_request_id', $requestId)
        ->orderByDesc('decided_at')
        ->orderByDesc('id')
        ->first(['decision_type', 'after_snapshot']);

    if ($dec && (string)$dec->decision_type === 'reject' && $dec->after_snapshot) {
        $after = is_string($dec->after_snapshot)
            ? json_decode($dec->after_snapshot, true)
            : (array)$dec->after_snapshot;

        // after_snapshot は { brand: {value,...}, condition: {value,...}, color: {value,...} } 形式
        $brand = data_get($after, 'brand.value');
        $condition = data_get($after, 'condition.value');
        $color = data_get($after, 'color.value');

        return [
            'brand' => is_string($brand) && trim($brand) !== ''
                ? ['name' => trim($brand), 'source' => 'raw']
                : null,

            'condition' => is_string($condition) && trim($condition) !== ''
                ? ['name' => trim($condition), 'source' => 'raw']
                : null,

            'color' => is_string($color) && trim($color) !== ''
                ? ['name' => trim($color), 'source' => 'raw']
                : null,

            'confidence_map' => null,

            'meta' => [
                'analysis_request_id' => $requestId,
                'item_id'             => $itemId,
                'item_draft_id'       => null,
                'status'              => 'rejected',
                'source'              => 'raw_reject',
            ],
        ];
    }

    // ✅ 3) それでも無ければ null（items の raw fallbackへ）
    return null;
}

    /**
     * ★ v3 固定：DecideUseCase 用（analysis_request_id 主語）
     */
    public function findLatestActiveByRequestId(int $analysisRequestId): ?array
    {
        $row = DB::table('analysis_results')
            ->where('analysis_request_id', $analysisRequestId)
            ->whereIn('status', ['active', 'provisional'])
            ->orderByDesc('id')
            ->first();

        return $this->mapRow($row);
    }

    /**
     * 共通マッピング
     */
    private function mapRow(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        return [
            'brand' => $row->brand_name
                ? [
                    'name'   => $row->brand_name,
                    'source' => $row->source ?? 'ai_provisional',
                ]
                : null,

            'condition' => $row->condition_name
                ? [
                    'name'   => $row->condition_name,
                    'source' => $row->source ?? 'ai_provisional',
                ]
                : null,

            'color' => $row->color_name
                ? [
                    'name'   => $row->color_name,
                    'source' => $row->source ?? 'ai_provisional',
                ]
                : null,

            'confidence_map' => $row->confidence_map
                ? json_decode($row->confidence_map, true)
                : null,

            'meta' => [
                'analysis_request_id' => (int) $row->analysis_request_id,
                'item_id'             => (int) $row->item_id,
                'item_draft_id'       => $row->item_draft_id,
                'status'              => $row->status,
                'source'              => $row->source,
            ],
        ];
    }
}