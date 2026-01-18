<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\LedgerQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentLedgerQueryRepository implements LedgerQueryRepository
{
    public function getSummary(int $shopId, string $fromDate, string $toDate): array
    {
        // from/to は YYYY-MM-DD を想定 → 日付範囲を日跨ぎで含める（JST運用）
        $from = $fromDate . ' 00:00:00';
        $to   = $toDate . ' 23:59:59';

        $rows = DB::table('ledger_postings')
            ->selectRaw("
                currency,
                SUM(CASE WHEN posting_type = 'sale' THEN amount ELSE 0 END) AS sales_total,
                SUM(CASE WHEN posting_type = 'refund' THEN amount ELSE 0 END) AS refund_total,
                COUNT(*) AS postings_count
            ")
            ->where('shop_id', $shopId)
            ->whereBetween('occurred_at', [$from, $to])
            ->groupBy('currency')
            ->get();

        // v2-2はまず単一通貨想定（JPY）
        if ($rows->count() === 0) {
            return [
                'currency' => 'JPY',
                'sales_total' => 0,
                'refund_total' => 0,
                'postings_count' => 0,
            ];
        }

        // 複数通貨が来るなら将来拡張（v2-2では最初の1つを返す）
        $r = $rows->first();

        return [
            'currency' => (string)$r->currency,
            'sales_total' => (int)$r->sales_total,
            'refund_total' => (int)$r->refund_total,
            'postings_count' => (int)$r->postings_count,
        ];
    }

    public function listPostingsWithEntries(
        int $shopId,
        string $fromDate,
        string $toDate,
        int $limit,
        ?int $cursorPostingId = null
    ): array {
        $from = $fromDate . ' 00:00:00';
        $to   = $toDate . ' 23:59:59';

        $q = DB::table('ledger_postings')
            ->where('shop_id', $shopId)
            ->whereBetween('occurred_at', [$from, $to]);

        // カーソル：posting_id より小さいものを取る（降順ページング）
        if (is_int($cursorPostingId)) {
            $q->where('id', '<', $cursorPostingId);
        }

        $postings = $q->orderByDesc('id')->limit($limit)->get();
        if ($postings->count() === 0) {
            return ['postings' => [], 'entries' => []];
        }

        $postingIds = $postings->pluck('id')->map(fn($x) => (int)$x)->all();

        $entries = DB::table('ledger_entries')
            ->whereIn('posting_id', $postingIds)
            ->orderByDesc('id')
            ->get()
            ->groupBy('posting_id');

        return [
            'postings' => $postings->map(fn($p) => (array)$p)->all(),
            'entries' => $entries->map(function ($group) {
                return $group->map(fn($e) => (array)$e)->all();
            })->all(),
        ];
    }
}