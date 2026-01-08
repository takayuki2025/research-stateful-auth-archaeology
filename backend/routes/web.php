<?php
// web.php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Modules\Auth\Presentation\Http\Controllers\RegisterController;

Route::post('/api/register', RegisterController::class)
    ->middleware(['web']);

use App\Modules\Auth\Presentation\Http\Controllers\LogoutController;

Route::post('/logout', LogoutController::class)
    ->middleware(['web']);

// health（任意）
Route::get('/health', fn () => ['status' => 'ok']);



/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/
// メール認証の際のメール再送信
use App\Modules\Auth\Presentation\Http\Controllers\ResendEmailVerificationController;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use Illuminate\Support\Facades\URL;

Route::post('/email/verification-notification',ResendEmailVerificationController::class)->middleware(['web']);
use Illuminate\Foundation\Auth\EmailVerificationRequest;



// 認証リンクを踏んだとき
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {

    $user = User::findOrFail($id);

    if (! hash_equals(
        sha1($user->getEmailForVerification()),
        $hash
    )) {
        abort(403, 'Invalid verification link.');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    // ★ SPA 遷移前提の URL
    return redirect()->away(
        config('app.frontend_url') . '/email/verified'
    );
})
->middleware('signed')
->name('verification.verify');

// 認証案内ページ（Laravel側では未使用だが name が必要）
// Route::get('/email/verify', function () {
//     return response()->json(['message' => 'verify email']);
// })->middleware('auth')->name('verification.notice');