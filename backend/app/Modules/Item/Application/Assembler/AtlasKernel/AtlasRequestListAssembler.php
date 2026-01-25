<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Assembler\AtlasKernel;

use stdClass;

final class AtlasRequestListAssembler
{
    /**
     * @param array<int,stdClass> $rows
     */
    public function assembleMany(array $rows): array
    {
        return array_map(fn ($r) => $this->assemble($r), $rows);
    }

    private function assemble(stdClass $r): array
    {
        $hasDiff =
            $r->diff_brand_before !== null || $r->diff_brand_after !== null ||
            $r->diff_condition_before !== null || $r->diff_condition_after !== null ||
            $r->diff_color_before !== null || $r->diff_color_after !== null;

        return [
            'request_id' => (int) $r->request_id,
            'shop_code'  => $r->shop_code,

            'item' => [
                'id'   => (int) $r->item_id,
                'name' => $r->item_name ?? 'Unknown',
            ],

            /* ===== Timeline ===== */
            'submitted_at' => $r->submitted_at,
            'analyzed_at'  => $r->analyzed_at,
            'decided_at'   => $r->decided_at ?? null,

            /* ===== Trigger ===== */
            'trigger' => [
                'by'     => $r->triggered_by_type,
                'reason' => $r->trigger_reason,
                'replay' => $r->original_request_id ? [
                    'original_request_id' => (int) $r->original_request_id,
                    'index' => (int) $r->replay_index,
                ] : null,
            ],

            /* ===== State ===== */
            'request_status' => $r->request_status,
            'error' => $r->error_code ? [
                'code' => $r->error_code,
                'message' => $r->error_message,
            ] : null,

            /* ===== Before (Human Input) ===== */
            'before' => [
                'brand'     => $r->before_brand,
                'condition' => $r->before_condition,
                'color'     => $r->before_color,
            ],

            /* ===== AI ===== */
            'ai' => [
                'brand'          => $r->ai_brand,
                'condition'      => $r->ai_condition,
                'color'          => $r->ai_color,
                'max_confidence' => $r->max_confidence !== null
                    ? (float) $r->max_confidence
                    : null,
                'source'         => $r->ai_source,
                'confidence_map' => $r->confidence_map
                    ? json_decode($r->confidence_map, true)
                    : null,
            ],

            /* ===== Decision ===== */
            'decision' => $r->decision_type ? [
                'type'       => $r->decision_type,
                'by'         => $r->decided_by_type,
                'decided_at' => $r->decided_at,
                'user'       => $r->user_id ? [
                    'id'   => (int) $r->user_id,
                    'name' => $r->user_name,
                ] : null,
            ] : null,

            /* ===== Diff ===== */
            'diff' => $hasDiff ? [
                'brand' => ['before' => $r->diff_brand_before, 'after' => $r->diff_brand_after],
                'condition' => ['before' => $r->diff_condition_before, 'after' => $r->diff_condition_after],
                'color' => ['before' => $r->diff_color_before, 'after' => $r->diff_color_after],
            ] : null,

            /* ===== Final ===== */
            'final' => [
    'brand' => $r->final_brand ?? null,
    'condition' => $r->final_condition ?? null,
    'color' => $r->final_color ?? null,
    'source' => $r->final_source ?? null,
    'max_confidence' => property_exists($r, 'final_max_confidence') && $r->final_max_confidence !== null
        ? (float) $r->final_max_confidence
        : null,
],
        ];
    }
}
