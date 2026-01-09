<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Models\User;
use App\Modules\Auth\Presentation\Http\Controllers\ResendEmailVerificationController;
// use App\Modules\Auth\Presentation\Http\Controllers\VerifyEmailController;
/*
|--------------------------------------------------------------------------
| Web Routes (Browser only)
|--------------------------------------------------------------------------
*/

// health check
Route::get('/health', fn () => ['status' => 'ok']);

/*
|--------------------------------------------------------------------------
| Email Verification (Web only)
|--------------------------------------------------------------------------
*/

// 認証メール再送（SPA から POST）
Route::post(
    '/email/verification-notification',
    ResendEmailVerificationController::class
)->middleware(['web']);

// 認証リンククリック（メールから）

// Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
//     ->middleware(['signed'])
//     ->name('verification.verify');