<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Application\Service;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class AtlasKernelAnalyzer
{
    public function analyze(int $analysisRequestId): void
    {
        // ① 冪等ガード（すでに decision があれば何もしない）
        $exists = DB::table('review_decisions')
            ->where('analysis_request_id', $analysisRequestId)
            ->exists();

        if ($exists) {
            return;
        }

        // ② Before snapshot（将来は entity / brand / tag 等）
        $beforeSnapshot = [
            'brand_raw' => 'あっぷる',
        ];

        // ③ AtlasKernel 解析（いまはダミー）
        $afterSnapshot = [
            'brand_normalized' => 'Apple',
            'confidence' => 0.62,
            'candidates' => [
                ['value' => 'Apple', 'score' => 0.62],
                ['value' => 'アップル', 'score' => 0.31],
            ],
        ];

        // ④ decision type を confidence で分岐（最小ロジック）
        $decisionType = $afterSnapshot['confidence'] >= 0.9
            ? 'system_approve'
            : 'edit_confirm';

        $now = CarbonImmutable::now();

        // ⑤ review_decisions に 1 行だけ書く
        DB::table('review_decisions')->insert([
            'analysis_request_id' => $analysisRequestId,

            'decision_type'   => $decisionType,
            'decision_reason' => 'auto_analysis',
            'note'            => null,

            'before_snapshot' => json_encode($beforeSnapshot, JSON_UNESCAPED_UNICODE),
            'after_snapshot'  => json_encode($afterSnapshot, JSON_UNESCAPED_UNICODE),

            'decided_by_type' => 'system',
            'decided_by'      => null,
            'decided_at'      => $now,

            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    
}