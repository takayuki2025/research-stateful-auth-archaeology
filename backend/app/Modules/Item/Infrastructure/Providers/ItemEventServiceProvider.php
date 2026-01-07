<?php

namespace App\Modules\Item\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

final class ItemEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        \App\Modules\Item\Domain\Event\ItemPublished::class => [
            \App\Modules\Item\Application\Listener\GenerateItemEntitiesOnItemPublished::class,
        ],
    ];
}
