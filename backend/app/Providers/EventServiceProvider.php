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
use App\Listeners\SetFirstLoginAtOnVerified;
use App\Modules\Item\Domain\Event\Atlas\AtlasManualOverrideOccurred;
use App\Listeners\NotifyAdminOnManualOverride;
use App\Modules\Item\Domain\Event\Atlas\ReviewDecisionMade;
use App\Modules\Item\Application\Listener\ApplyConfirmedDecisionListener;

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
            \App\Listeners\CreateInitialProfile::class,
        ],

        Verified::class => [
            \App\Listeners\RedirectAfterEmailVerified::class,
            SetFirstLoginAtOnVerified::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Item / Atlas
        |--------------------------------------------------------------------------
        */
        \App\Modules\Item\Application\Event\ItemImported::class => [
            \App\Modules\Item\Application\Listener\AnalyzeImportedItemListener::class,
        ],

        \App\Modules\Item\Domain\Event\Atlas\AtlasManualOverrideOccurred::class => [
            \App\Listeners\NotifyAdminOnManualOverride::class,
        ],

        ReviewDecisionMade::class => [
        ApplyConfirmedDecisionListener::class,
    ],

        /*
        |--------------------------------------------------------------------------
        | Domain Events
        |--------------------------------------------------------------------------
        */
        OrderPaid::class => [
            OnOrderPaidRecordOrderHistory::class,
            EnsureShopAddressOnOrderPaid::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}