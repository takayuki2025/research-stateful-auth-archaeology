<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
{
    parent::setUp();

    // AnalysisRequestRepository
    $this->app->bind(
        \App\Modules\Item\Domain\Repository\AnalysisRequestRepository::class,
        \App\Modules\Item\Infrastructure\Persistence\Repository\EloquentAnalysisRequestRepository::class
    );

    // AtlasKernelPort（mock 可能にするため）
    $this->app->bind(
        \App\Modules\Item\Domain\Service\AtlasKernelPort::class,
        \App\Modules\Item\Domain\Service\AtlasKernelService::class
    );
}
}