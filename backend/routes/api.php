<?php
// api.php
use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Presentation\Http\Controllers\MeController;
use App\Modules\Auth\Presentation\Http\Controllers\LoginController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);
});
Route::post('/login', LoginController::class);



