<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class JwtAuthProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::viaRequest('jwt', function ($request) {
            return $request->user(); // JwtAuthenticate でセットした user を返す
        });
    }
}
