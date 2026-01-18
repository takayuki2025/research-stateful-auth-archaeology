<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\LedgerEntriesOutput;
use App\Modules\Payment\Application\Dto\Ledger\LedgerEntryItemOutput;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerQueryRepository;

final class GetLedgerEntriesUseCase
{
    public function __construct(
        private LedgerQueryRepository $queries,
    ) {
    }

    public function handle(int $shopId, string $from, string $to, int $limit, ?int $cursor): LedgerEntriesOutput
    {
        $limit = max(1, min($limit, 200));

        $result = $this->queries->listPostingsWithEntries($shopId, $from, $to, $limit, $cursor);

        $postings = $result['postings'];
        $entriesMap = $result['entries'];

        $items = [];

        foreach ($postings as $p) {
            $pid = (int)$p['id'];

            $entries = $entriesMap[$pid] ?? [];
            $simpleEntries = array_map(function ($e) {
                return [
                    'account_code' => (string)$e['account_code'],
                    'side' => (string)$e['side'],
                    'amount' => (int)$e['amount'],
                ];
            }, $entries);

            $items[] = new LedgerEntryItemOutput(
                posting_id: $pid,
                occurred_at: (string)$p['occurred_at'],
                posting_type: (string)$p['posting_type'],
                order_id: isset($p['order_id']) ? (int)$p['order_id'] : null,
                payment_id: isset($p['payment_id']) ? (int)$p['payment_id'] : null,
                source_provider: (string)$p['source_provider'],
                source_event_id: (string)$p['source_event_id'],
                currency: (string)$p['currency'],
                entries: $simpleEntries,
            );
        }

        // next_cursor: 最後の posting_id（降順なので、最後が次ページのカーソル）
        $nextCursor = null;
        if (count($postings) === $limit) {
            $last = end($postings);
            $nextCursor = (int)$last['id'];
        }

        return new LedgerEntriesOutput($items, $nextCursor);
    }
}