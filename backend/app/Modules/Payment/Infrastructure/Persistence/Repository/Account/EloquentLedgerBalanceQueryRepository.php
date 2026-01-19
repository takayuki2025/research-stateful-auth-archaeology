<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\LedgerBalanceQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentLedgerBalanceQueryRepository implements LedgerBalanceQueryRepository
{
    public function sumCashClearingNet(int $shopId, string $currency, string $fromDate, string $toDate): int
    {
        $from = $fromDate . ' 00:00:00';
        $to   = $toDate . ' 23:59:59';

        // shop_id は ledger_postings から辿る（posting_id -> postings -> shop_id）
        $rows = DB::table('ledger_entries as e')
            ->join('ledger_postings as p', 'p.id', '=', 'e.posting_id')
            ->where('p.shop_id', $shopId)
            ->whereBetween('p.occurred_at', [$from, $to])
            ->where('e.currency', $currency)
            ->where('e.account_code', 'CASH_CLEARING')
            ->selectRaw("
                SUM(CASE WHEN e.side = 'debit' THEN e.amount ELSE 0 END) AS debit_sum,
                SUM(CASE WHEN e.side = 'credit' THEN e.amount ELSE 0 END) AS credit_sum
            ")
            ->first();

        $debit = (int)($rows->debit_sum ?? 0);
        $credit = (int)($rows->credit_sum ?? 0);

        return $debit - $credit;
    }
}