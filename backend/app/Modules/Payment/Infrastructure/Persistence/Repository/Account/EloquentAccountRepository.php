<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Account;

use App\Modules\Payment\Domain\Account\Repository\AccountRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentAccountRepository implements AccountRepository
{
    public function findOrCreateForShop(int $shopId, string $currency): int
    {
        $row = DB::table('accounts')
            ->where('account_owner_type', 'shop')
            ->where('account_owner_id', $shopId)
            ->where('currency', $currency)
            ->first();

        if ($row) {
            return (int)$row->id;
        }

        try {
            return (int) DB::table('accounts')->insertGetId([
                'account_owner_type' => 'shop',
                'account_owner_id' => $shopId,
                'currency' => $currency,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {
            // 競合でユニーク違反→再取得
            $row2 = DB::table('accounts')
                ->where('account_owner_type', 'shop')
                ->where('account_owner_id', $shopId)
                ->where('currency', $currency)
                ->first();

            if ($row2) return (int)$row2->id;
            throw $e;
        }
    }
}