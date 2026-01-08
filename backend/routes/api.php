<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Modules\Auth\Presentation\Http\Controllers\MeController;
use App\Modules\Auth\Presentation\Http\Controllers\LoginController;
use App\Modules\Auth\Presentation\Http\Controllers\RegisterController;
use App\Modules\Auth\Presentation\Http\Controllers\ConfirmFirstLoginController;

/*
|--------------------------------------------------------------------------
| Auth API (SPA + Sanctum)
|--------------------------------------------------------------------------
*/

Route::post('/login', LoginController::class);

Route::post('/register', RegisterController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);

    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->noContent();
    });

    Route::post('/auth/first-login', ConfirmFirstLoginController::class);
});





use App\Modules\Item\Presentation\Http\Controllers\PublicCatalogController;

Route::get('/items/public', PublicCatalogController::class);