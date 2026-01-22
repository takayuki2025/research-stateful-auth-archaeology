<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes (Browser only) web.php
|--------------------------------------------------------------------------
*/

// health check
Route::get('/health', fn () => ['status' => 'ok']);



// AWS
// Route::get('/', function () {
//     return response('ok', 200);
// });

/*
|--------------------------------------------------------------------------
| Email Verification (Web only)
|--------------------------------------------------------------------------
*/
use App\Modules\Auth\Presentation\Http\Controllers\ResendEmailVerificationController;

// 認証メール再送（SPA から POST）
Route::post(
    '/email/verification-notification',
    ResendEmailVerificationController::class
)->middleware(['web']);


/*
|--------------------------------------------------------------------------
ログイン・ログアウト
|--------------------------------------------------------------------------
*/
use App\Modules\Auth\Presentation\Http\Controllers\LoginController;
use App\Modules\Auth\Presentation\Http\Controllers\LogoutController;

Route::post('/login', LoginController::class);

Route::post('/logout', LogoutController::class);