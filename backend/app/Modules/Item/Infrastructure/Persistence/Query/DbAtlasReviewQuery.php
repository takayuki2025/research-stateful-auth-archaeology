<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use App\Modules\Item\Application\Query\AtlasReviewQuery;
use Illuminate\Support\Facades\DB;

final class DbAtlasReviewQuery implements AtlasReviewQuery
{
    public function fetchReviewSource(string $shopCode, int $analysisRequestId): array
    {
        // =========================
        // 1) Request / Item / Shop
        // =========================
        $req = DB::table('analysis_requests as ar')
            ->join('items as i', 'i.id', '=', 'ar.item_id')
            ->join('shops as s', 's.id', '=', 'i.shop_id')
            ->where('s.shop_code', $shopCode)
            ->where('ar.id', $analysisRequestId)
            ->select([
                'ar.id as request_id',
                'ar.item_id',
                'ar.status',
                'ar.analysis_version',
            ])
            ->first();

        if (! $req) {
            abort(404, 'analysis_request not found');
        }

        // =========================
        // 2) BEFORE（現行 item_entities）
        // =========================
        $beforeEntity = DB::table('item_entities as ie')
    ->leftJoin('brand_entities as be', 'ie.brand_entity_id', '=', 'be.id')
    ->leftJoin('condition_entities as ce', 'ie.condition_entity_id', '=', 'ce.id')
    ->leftJoin('color_entities as coe', 'ie.color_entity_id', '=', 'coe.id')
    ->where('ie.item_id', $req->item_id)
    ->where('ie.is_latest', true)
    ->select([
        'be.canonical_name as brand',
        'ce.canonical_name as condition',
        'coe.canonical_name as color',
    ])
    ->first();

$before = $beforeEntity ? [
    'brand'     => $beforeEntity->brand ?? null,
    'condition' => $beforeEntity->condition ?? null,
    'color'     => $beforeEntity->color ?? null,
] : [];

        // =========================
        // 3) AFTER（analysis_results）
        // =========================
        $result = DB::table('analysis_results')
            ->where('analysis_request_id', $analysisRequestId)
            ->first();

        $after = $result ? [
            'brand'     => $result->brand_name,
            'condition' => $result->condition_name,
            'color'     => $result->color_name,
        ] : [];

        // =========================
        // 4) Attributes + Confidence
        // =========================
        $attributes = [
            'brand' => [
                'value' => $after['brand'] ?? null,
                'confidence' => $result->brand_confidence ?? null,
            ],
            'condition' => [
                'value' => $after['condition'] ?? null,
                'confidence' => $result->condition_confidence ?? null,
            ],
            'color' => [
                'value' => $after['color'] ?? null,
                'confidence' => $result->color_confidence ?? null,
            ],
        ];

        return [
            'request' => (array)$req,
            'before' => $before,
            'after' => $after,
            'attributes' => $attributes,
        ];
    }
}