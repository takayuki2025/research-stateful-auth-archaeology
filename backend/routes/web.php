<?php
// web.php
use Illuminate\Support\Facades\Route;
// use App\Modules\Auth\Presentation\Http\Controllers\LoginController;
use App\Modules\Auth\Presentation\Http\Controllers\LogoutController;



Route::post('/logout', LogoutController::class)
    ->middleware(['web']);

// health（任意）
Route::get('/health', fn () => ['status' => 'ok']);