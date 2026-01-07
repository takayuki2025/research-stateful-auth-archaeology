<?php

use Illuminate\Support\Facades\Route;
// use App\Modules\Auth\Presentation\Http\Controllers\VerifyEmailController;

//メール認証送信
// Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
// ->middleware(['signed'])->name('verification.verify');


// ========== API サーバーとしての root ==========
Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel API is running.',
        'time' => now()->toDateTimeString(),
    ]);
});

