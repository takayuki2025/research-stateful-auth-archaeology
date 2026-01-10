<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAnalysisResultRepository implements AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void
{
    // payload が「全体」でも「analysisだけ」でも動くように吸収
    $analysis = $payload['analysis'] ?? $payload;

    $canonical = data_get($analysis, 'integration.brand_identity.canonical');
    $confBrand = (float) data_get($analysis, 'integration.brand_identity.confidence', 0.0);

    $condition = data_get($analysis, 'extraction.condition', []);
    $color     = data_get($analysis, 'extraction.color', []);

    $model     = data_get($analysis, 'lineage.model', 'AtlasKernel-unknown');
    $rawText   = data_get($payload, 'input.raw_text') ?? data_get($analysis, 'input.raw_text');

    DB::table('analysis_results')->insert([
        'item_id' => $itemId,

        // 生の完全保存（あとで絶対効く）
        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),

        // Review 用（UIが読むのは基本ここ）
        'tags' => json_encode([
            'brand'     => $canonical,
            'condition' => $condition,
            'color'     => $color,
        ], JSON_UNESCAPED_UNICODE),

        'confidence' => json_encode([
            'brand' => $confBrand,
        ], JSON_UNESCAPED_UNICODE),

        'generated_version' => $model,
        'raw_text'          => $rawText,

        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

    public function markRejected(int $itemId): void
    {
        DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->update([
                'status'     => 'rejected',
                'updated_at' => now(),
            ]);
    }
}