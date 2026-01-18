<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\CustomerWallet;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use Illuminate\Support\Facades\DB;

final class EloquentWalletRepository implements WalletRepository
{
    public function findByUserId(int $userId, string $provider = 'stripe'): ?CustomerWallet
    {
        $row = DB::table('customer_wallets')
            ->where('user_id', $userId)
            ->where('provider', $provider)
            ->first();

        if (! $row) {
            return null;
        }

        return CustomerWallet::reconstitute(
            id: (int)$row->id,
            userId: (int)$row->user_id,
            shopId: $row->shop_id !== null ? (int)$row->shop_id : null,
            provider: (string)$row->provider,
            providerCustomerId: $row->provider_customer_id ?: null,
            status: (string)$row->status,
            meta: $row->meta ? json_decode($row->meta, true) : null,
        );
    }

    public function create(CustomerWallet $wallet): CustomerWallet
    {
        $id = DB::table('customer_wallets')->insertGetId([
            'user_id' => $wallet->userId(),
            'shop_id' => $wallet->shopId(),
            'provider' => $wallet->provider(),
            'provider_customer_id' => $wallet->providerCustomerId(),
            'status' => $wallet->status(),
            'meta' => $wallet->meta() ? json_encode($wallet->meta(), JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return CustomerWallet::reconstitute(
            id: (int)$id,
            userId: $wallet->userId(),
            shopId: $wallet->shopId(),
            provider: $wallet->provider(),
            providerCustomerId: $wallet->providerCustomerId(),
            status: $wallet->status(),
            meta: $wallet->meta(),
        );
    }
}