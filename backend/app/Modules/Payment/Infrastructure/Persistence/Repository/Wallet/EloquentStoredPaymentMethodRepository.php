<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\StoredPaymentMethod;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use Illuminate\Support\Facades\DB;

final class EloquentStoredPaymentMethodRepository implements StoredPaymentMethodRepository
{
    public function listActiveByWalletId(int $walletId): array
    {
        $rows = DB::table('stored_payment_methods')
            ->where('wallet_id', $walletId)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return $rows->map(function ($row) {
            return StoredPaymentMethod::reconstitute(
                id: (int)$row->id,
                walletId: (int)$row->wallet_id,
                provider: (string)$row->provider,
                providerPaymentMethodId: (string)$row->provider_payment_method_id,
                brand: $row->brand ?: null,
                last4: $row->last4 ?: null,
                expMonth: $row->exp_month !== null ? (int)$row->exp_month : null,
                expYear: $row->exp_year !== null ? (int)$row->exp_year : null,
                isDefault: (bool)$row->is_default,
                status: (string)$row->status,
                meta: $row->meta ? json_decode($row->meta, true) : null,
            );
        })->all();
    }
}