<?php

namespace App\Modules\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Wallet\EloquentWalletRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Wallet\EloquentStoredPaymentMethodRepository;
use App\Modules\Payment\Domain\Service\PaymentMethodVault;
use App\Modules\Payment\Infrastructure\Gateway\StripePaymentMethodVault;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerPostingRepository;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerEntryRepository;
use App\Modules\Payment\Domain\Ledger\Service\LedgerPoster;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger\EloquentLedgerPostingRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger\EloquentLedgerEntryRepository;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerQueryRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger\EloquentLedgerQueryRepository;
use App\Modules\Payment\Domain\Port\FeeQueryPort;
use App\Modules\Payment\Infrastructure\Gateway\StripeFeeQuery;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerReconciliationQueryRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger\EloquentLedgerReconciliationQueryRepository;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerPort;
use App\Modules\Payment\Infrastructure\Ledger\LocalPostLedgerPort;
use App\Modules\Payment\Domain\Account\Repository\AccountRepository;
use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use App\Modules\Payment\Domain\Account\Repository\LedgerBalanceQueryRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Account\EloquentAccountRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Account\EloquentBalanceRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Account\EloquentLedgerBalanceQueryRepository;
use App\Modules\Payment\Domain\Account\Repository\HoldRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Account\EloquentHoldRepository;

final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
{
    $this->app->bind(PaymentMethodVault::class, StripePaymentMethodVault::class);

    $this->app->bind(WalletRepository::class, EloquentWalletRepository::class);
    $this->app->bind(StoredPaymentMethodRepository::class, EloquentStoredPaymentMethodRepository::class);

    $this->app->bind(LedgerPostingRepository::class, EloquentLedgerPostingRepository::class);
    $this->app->bind(LedgerEntryRepository::class, EloquentLedgerEntryRepository::class);
    $this->app->singleton(LedgerPoster::class, fn () => new LedgerPoster());
    $this->app->bind(LedgerQueryRepository::class, EloquentLedgerQueryRepository::class);
    $this->app->bind(FeeQueryPort::class, StripeFeeQuery::class);
    $this->app->bind(LedgerReconciliationQueryRepository::class, EloquentLedgerReconciliationQueryRepository::class);
    $this->app->bind(PostLedgerPort::class, LocalPostLedgerPort::class);
    $this->app->bind(AccountRepository::class, EloquentAccountRepository::class);
    $this->app->bind(BalanceRepository::class, EloquentBalanceRepository::class);
    $this->app->bind(LedgerBalanceQueryRepository::class, EloquentLedgerBalanceQueryRepository::class);
    $this->app->bind(HoldRepository::class, EloquentHoldRepository::class);
}
}
