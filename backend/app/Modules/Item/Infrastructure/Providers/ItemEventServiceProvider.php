<?php

namespace App\Modules\Item\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

final class ItemEventServiceProvider extends EventServiceProvider
{
    /**
     * v3整理：解析経路を ItemImported 一本に固定するため、
     * ItemPublished -> GenerateItemEntities 系は当面OFF
     * （GenerateItemEntitiesJob 不在/責務不整合の事故源）
     */
    protected $listen = [
        // \App\Modules\Item\Domain\Event\ItemPublished::class => [
        //     \App\Modules\Item\Application\Listener\GenerateItemEntitiesOnItemPublished::class,
        // ],
    ];
}