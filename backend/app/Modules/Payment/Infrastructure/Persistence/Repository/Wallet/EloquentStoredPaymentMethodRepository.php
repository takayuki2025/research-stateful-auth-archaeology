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
                source: (string)($row->source ?? 'card'),
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

    public function upsertActiveCard(
    int $walletId,
    string $provider,
    string $providerPaymentMethodId,
    ?string $brand,
    ?string $last4,
    ?int $expMonth,
    ?int $expYear,
): void {
    // 既存確認
    $existing = DB::table('stored_payment_methods')
        ->where('wallet_id', $walletId)
        ->where('provider', $provider)
        ->where('provider_payment_method_id', $providerPaymentMethodId)
        ->first();

    if (! $existing) {
        // 初回の1枚目は default=true（強制）
        $hasAny = DB::table('stored_payment_methods')
            ->where('wallet_id', $walletId)
            ->where('status', 'active')
            ->exists();

        DB::table('stored_payment_methods')->insert([
            'wallet_id' => $walletId,
            'provider' => $provider,
            'provider_payment_method_id' => $providerPaymentMethodId,
            'source' => 'card',
            'brand' => $brand,
            'last4' => $last4,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
            'is_default' => $hasAny ? 0 : 1,
            'status' => 'active',
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return;
    }

    // 既存更新（カード情報の同期）
    DB::table('stored_payment_methods')
        ->where('id', (int)$existing->id)
        ->update([
            'source' => 'card',
            'brand' => $brand,
            'last4' => $last4,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
            'status' => 'active',
            'updated_at' => now(),
        ]);
}

public function findRowById(int $id): ?array
{
    $row = DB::table('stored_payment_methods')->where('id', $id)->first();
    if (! $row) {
        return null;
    }

    return [
        'id' => (int)$row->id,
        'wallet_id' => (int)$row->wallet_id,
        'provider' => (string)$row->provider,
        'provider_payment_method_id' => (string)$row->provider_payment_method_id,
        'is_default' => (bool)$row->is_default,
        'status' => (string)$row->status,
        'source' => (string)($row->source ?? 'card'),
    ];
}

public function setDefault(int $walletId, int $methodId): void
{
    // wallet内のdefaultを全解除（activeのみ）
    DB::table('stored_payment_methods')
        ->where('wallet_id', $walletId)
        ->where('status', 'active')
        ->update([
            'is_default' => 0,
            'updated_at' => now(),
        ]);

    // 指定IDをdefaultに
    DB::table('stored_payment_methods')
        ->where('wallet_id', $walletId)
        ->where('id', $methodId)
        ->where('status', 'active')
        ->update([
            'is_default' => 1,
            'updated_at' => now(),
        ]);
}

public function markDetached(int $walletId, int $methodId): void
{
    DB::table('stored_payment_methods')
        ->where('wallet_id', $walletId)
        ->where('id', $methodId)
        ->update([
            'status' => 'detached',
            'is_default' => 0,
            'updated_at' => now(),
        ]);
}

public function findNextActiveId(int $walletId, int $excludeId): ?int
{
    $row = DB::table('stored_payment_methods')
        ->where('wallet_id', $walletId)
        ->where('status', 'active')
        ->where('id', '<>', $excludeId)
        ->orderByDesc('id')
        ->first();

    return $row ? (int)$row->id : null;
}

public function findDefaultActiveRow(int $walletId): ?array
{
    $row = DB::table('stored_payment_methods')
        ->where('wallet_id', $walletId)
        ->where('status', 'active')
        ->where('is_default', 1)
        ->first();

    if (! $row) {
        return null;
    }

    return [
        'id' => (int)$row->id,
        'wallet_id' => (int)$row->wallet_id,
        'provider' => (string)$row->provider,
        'provider_payment_method_id' => (string)$row->provider_payment_method_id,
        'is_default' => (bool)$row->is_default,
        'status' => (string)$row->status,
        'source' => (string)($row->source ?? 'card'),
    ];
}
}