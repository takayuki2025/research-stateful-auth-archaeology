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
}
}
