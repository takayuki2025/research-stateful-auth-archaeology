<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use App\Modules\Payment\Infrastructure\Gateway\StripePaymentGateway;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Payment\Domain\Repository\PaymentQueryRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\EloquentPaymentRepository;
use App\Modules\Payment\Infrastructure\Persistence\Repository\EloquentPaymentQueryRepository;

final class PaymentModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayPort::class, StripePaymentGateway::class);
        $this->app->bind(PaymentRepository::class, EloquentPaymentRepository::class);
        $this->app->bind(PaymentQueryRepository::class, EloquentPaymentQueryRepository::class);
    }
}
