<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\LedgerEntryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentLedgerEntryRepository implements LedgerEntryRepository
{
    public function insertEntries(int $postingId, array $rows): void
    {
        // $rows: [['account_code'=>..., 'side'=>..., 'amount'=>..., 'currency'=>..., 'meta'=>...], ...]
        $now = now();

        $payload = array_map(function (array $r) use ($postingId, $now) {
            return [
                'posting_id' => $postingId,
                'account_code' => $r['account_code'],
                'side' => $r['side'],
                'amount' => $r['amount'],
                'currency' => $r['currency'],
                'meta' => isset($r['meta']) ? json_encode($r['meta'], JSON_UNESCAPED_UNICODE) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows);

        DB::table('ledger_entries')->insert($payload);
    }
}