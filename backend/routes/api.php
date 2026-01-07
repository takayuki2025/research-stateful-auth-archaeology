<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;

// ==========================
// Auth (Stateful SPA)
// ==========================

// CSRF Cookie（最初に必ず叩く）
Route::get('/sanctum/csrf-cookie', fn () => response()->noContent());

// Login
Route::post('/login', LoginController::class);

// Logout
Route::post('/logout', LogoutController::class)
    ->middleware('auth:web');

// Me（認証起点）
Route::get('/me', MeController::class)
    ->middleware('auth:web');
