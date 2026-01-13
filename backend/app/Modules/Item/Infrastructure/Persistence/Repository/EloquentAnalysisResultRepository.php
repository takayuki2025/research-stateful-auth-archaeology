<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAnalysisResultRepository implements AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void
    {
        /**
         * v3固定ルール：
         * - analysis_request_id は payload から取得
         * - Repository の引数は変えない
         */
        $analysisRequestId = $payload['request_id']
            ?? throw new \InvalidArgumentException('payload.request_id is required');

        DB::table('analysis_results')->insert([
            'analysis_request_id' => $analysisRequestId,
            'item_id'             => $itemId,

            // 正規化候補（After）
            'brand_name'     => data_get($payload, 'analysis.extraction.brand'),
            'condition_name' => data_get($payload, 'analysis.extraction.condition'),
            'color_name'     => data_get($payload, 'analysis.extraction.color'),

            // v3固定：confidence は JSON + overall
            'confidence_map'     => json_encode(
                data_get($payload, 'analysis.confidence_map', []),
                JSON_UNESCAPED_UNICODE
            ),
            'overall_confidence' => data_get($payload, 'analysis.overall_confidence'),

            // 根拠（AI・Rule・将来再解析用）
            'evidence' => json_encode(
                data_get($payload, 'analysis.evidence'),
                JSON_UNESCAPED_UNICODE
            ),

            // 技術状態のみ
            'status' => 'active',

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function supersedeByItem(int $itemId): void
    {
        DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->update([
                'status'     => 'superseded',
                'updated_at'=> now(),
            ]);
    }

    public function findByRequestId(int $analysisRequestId): ?object
    {
        return DB::table('analysis_results')
            ->where('analysis_request_id', $analysisRequestId)
            ->orderByDesc('id')
            ->first();
    }
}