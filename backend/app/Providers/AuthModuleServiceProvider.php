<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Auth モジュールの UseCase / Service / Repository
use App\Modules\Auth\Application\UseCase\VerifyEmailUseCase;
use App\Modules\Auth\Application\UseCase\LoginUseCase;
use App\Modules\Auth\Application\UseCase\RegisterUseCase;
use App\Modules\Auth\Infrastructure\External\FirebaseProvider;
use App\Modules\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Modules\Auth\Infrastructure\Persistence\AuthUserRepository;

class AuthModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Repository binding
        $this->app->bind(
            AuthUserRepositoryInterface::class,
            AuthUserRepository::class
        );

        // FirebaseProvider をシングルトンで登録
        $this->app->singleton(FirebaseProvider::class, function ($app) {
            return new FirebaseProvider();
        });
    }

    public function boot()
    {
        //
    }
}
