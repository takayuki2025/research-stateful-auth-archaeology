<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Auth\Application\Context\AuthContext;

final class AuthContextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ★ Request スコープ
        $this->app->scoped(AuthContext::class, function () {
            return new AuthContext();
        });
    }
}
