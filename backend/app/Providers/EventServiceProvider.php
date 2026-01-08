<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Verified;
// Domain Event
use App\Modules\Order\Domain\Event\OrderPaid;
// Listeners
// use App\Modules\Shipment\Infrastructure\EventListener\OnOrderPaidCreateShipmentDraft;
use App\Modules\Order\Infrastructure\EventListener\OnOrderPaidRecordOrderHistory;
use App\Modules\Shipment\Application\Listener\CreateShipmentOnOrderPaid;
use App\Modules\Shop\Infrastructure\Listener\EnsureShopAddressOnOrderPaid;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        /*
        |--------------------------------------------------------------------------
        | Laravel 標準イベント
        |--------------------------------------------------------------------------
        */
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        Verified::class => [
            \App\Listeners\RedirectAfterEmailVerified::class,
        ],

        \Illuminate\Auth\Events\Verified::class => [
        \App\Listeners\SetFirstLoginAtOnVerified::class,
    ],
        /*
        |--------------------------------------------------------------------------
        | Domain Events（唯一の定義）
        |--------------------------------------------------------------------------
        | OrderPaid = 支払いが確定したという「業務的事実」
        | ここから副作用（配送・履歴）を派生させる
        */
        OrderPaid::class => [
            OnOrderPaidRecordOrderHistory::class,
            // CreateShipmentOnOrderPaid::class,
            EnsureShopAddressOnOrderPaid::class,
        ],
    ];

    public function boot(): void
    {
        // 何もしない（明示的でOK）
    }
}
