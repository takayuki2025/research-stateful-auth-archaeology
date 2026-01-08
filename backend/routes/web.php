<?php
// web.php
use Illuminate\Support\Facades\Route;




use App\Modules\Auth\Presentation\Http\Controllers\LogoutController;

Route::post('/logout', LogoutController::class)
    ->middleware(['web']);

// health（任意）
Route::get('/health', fn () => ['status' => 'ok']);




// メール認証の際のメール再送信
use App\Modules\Auth\Presentation\Http\Controllers\ResendEmailVerificationController;

Route::post('/email/verification-notification',ResendEmailVerificationController::class)->middleware(['web']);