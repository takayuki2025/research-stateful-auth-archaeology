<?php

namespace App\Modules\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Wallet\EloquentWalletRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\Wallet\EloquentStoredPaymentMethodRepository;
use App\Modules\Payment\Domain\Service\PaymentMethodVault;
use App\Modules\Payment\Infrastructure\Gateway\StripePaymentMethodVault;




final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
{
    $this->app->bind(PaymentMethodVault::class, StripePaymentMethodVault::class);

    $this->app->bind(WalletRepository::class, EloquentWalletRepository::class);
    $this->app->bind(StoredPaymentMethodRepository::class, EloquentStoredPaymentMethodRepository::class);
}
}
