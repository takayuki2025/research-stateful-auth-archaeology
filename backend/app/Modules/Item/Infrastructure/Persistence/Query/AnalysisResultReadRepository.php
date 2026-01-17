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
    $row = DB::table('analysis_results')
        ->where('item_id', $itemId)
        ->whereIn('status', ['active', 'provisional'])
        ->orderByDesc('analysis_request_id') // ★重要
        ->orderByDesc('id')
        ->first();

        if (! $row) {
            return null;
        }

        return [
            // ★ display は必ずこの配下
            'brand' => $row->brand_name
                ? [
                    'name'   => $row->brand_name,
                    'source' => $row->source ?? 'ai_provisional',
                ]
                : null,

            'condition' => $row->condition_name
                ? [
                    'name' => $row->condition_name,
                ]
                : null,

            'color' => $row->color_name
                ? [
                    'name' => $row->color_name,
                ]
                : null,

            // ★ UI / デバッグ / 将来用
            'confidence_map' => $row->confidence_map
                ? json_decode($row->confidence_map, true)
                : null,

            // ★ v3 重要：主語の橋渡し
            'meta' => [
                'analysis_request_id' => (int) $row->analysis_request_id,
                'item_draft_id'       => $row->item_draft_id,
                'status'              => $row->status,
                'source'              => $row->source,
            ],
        ];
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