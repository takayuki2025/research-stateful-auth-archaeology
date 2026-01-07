<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Auth\AuthController; // AuthControllerを使用
// use App\Http\Controllers\Auth\EmailVerificationController; // 削除: AuthControllerに統合済み
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



// フロントページを表示し、持続検索機能とタブの切り替えを処理をするルーティング。
Route::get('/', [ItemController::class, 'index'])->name('front_page');

// 認証後のいろいろな処理を扱うルーティング
Route::get('/onetime', [ItemController::class, 'handleOnetimeRedirect'])->name('onetime.show');


// =========================================================================
// ★★★ カスタム認証ルート定義（Fortifyの機能を使わずに構築） ★★★
// =========================================================================

// メール認証関連のルート (AuthControllerを使用)
// メール認証通知ページを表示するルート
Route::get('/email/verify', [AuthController::class, 'notice']) // 修正
    ->middleware('auth')
    ->name('verification.notice');

// メール認証リクエストを処理するルート
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify']) // 修正
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// メール認証通知を再送信するルート
Route::post('/email/verification-notification', [AuthController::class, 'resend']) // 修正
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Fortifyが提供するデフォルトのログアウト処理
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// 主に基本設計書の順に並んでいます。
Route::get('/item/{item_id}', [ItemController::class, 'item_detail_show'])->name('item_detail');

Route::get('/purchase/{item_id}', [ItemController::class, 'item_buy_show'])->middleware(['auth'])->name('item_buy');

Route::patch('/purchase/address/{item_id}/{user_id?}', [ItemController::class, 'update'])->name('item.purchase.update');
Route::get('/purchase/address/{item_id}/{user_id?}', [ItemController::class, 'item_purchase_edit'])->name('item.purchase.edit');

Route::get('/sell', [ItemController::class, 'item_sell_show'])->middleware(['auth'])->name('item_sell');

Route::get('/mypage', [ItemController::class, 'profile_show'])->middleware(['auth'])->name('profile');

// 🌟 修正：メール認証コントローラのリダイレクト先とテストが期待するルート 🌟
Route::get('/mypage/profile', [ItemController::class, 'profile_revise'])->middleware(['auth'])->name('profile_edit');

Route::post('/thanks_sell', [ItemController::class, 'thanks_sell_create']);
Route::get('/thanks_sell', [ItemController::class, 'thanks_sell_create']);


//購入処理（コンビニ払い完了処理まで/カード支払いstripe決済に繋げる処理）のルード
Route::post('/thanks_buy', [ItemController::class, 'thanks_buy_create'])->name('thanks_buy_create');
// カード支払いstripeでの処理
Route::get('/stripe_success', [ItemController::class, 'stripeSuccess'])->name('stripe_success');
// コンビニ/カード支払い共に処理完了後のページ移動のルード
Route::get('/thanks_buy', [ItemController::class, 'thanks_buy_show'])->name('thanks_buy');


// ユーザー情報の更新、出品商品登録、コメント投稿、いいね機能
Route::patch('/profile_update', [ItemController::class, 'profile_update']);

Route::post('/upload2', [ItemController::class, 'user_image_upload']);

Route::post('/upload', [ItemController::class, 'item_image_upload']);

Route::post('/comment_read', [ItemController::class, 'comment_create'])->name('comment_create');

Route::post('/items/{item}/favorite', [ItemController::class, 'favorite'])->name('item.favorite');


// mailhog受信テスト用
Route::get('/send-test-email', function () {
    try {
        Mail::raw('This is a test email from Laravel.', function (\Illuminate\Mail\Message $message) {
            $message->to('test@example.com')->subject('Test Email');
        });
        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage();
    }
});

// =========================================================================
// Fortifyコントローラへの参照とカスタムAuthControllerへの参照を整理します。
// =========================================================================

Route::group(['middleware' => ['web']], function () {
    $fortifyControllers = 'Laravel\Fortify\Http\Controllers\\';
    
    // 登録ルート (AuthControllerを使用)
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister']);

    // ログインルート (AuthControllerを使用 - カスタムリクエスト/ロジック適用)
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login'); // ★ 修正
    Route::post('/login', [AuthController::class, 'storeLogin']); // ★ 修正

    // パスワードリセットルート (Fortifyを使用)
    Route::get('/forgot-password', $fortifyControllers . 'PasswordResetLinkController@create')->name('password.request');
    Route::post('/forgot-password', $fortifyControllers . 'PasswordResetLinkController@store')->name('password.email');
    Route::get('/reset-password/{token}', $fortifyControllers . 'NewPasswordController@create')->name('password.reset');
    Route::post('/reset-password', $fortifyControllers . 'NewPasswordController@store')->name('password.update');
    
    // プロフィール情報更新ルート (Fortifyを使用)
    Route::put('/user/profile-information', $fortifyControllers . 'ProfileInformationController@update')->middleware(['auth'])->name('user-profile-information.update');
    
    // パスワード更新ルート (Fortifyを使用)
    Route::put('/user/password', $fortifyControllers . 'PasswordController@update')->middleware(['auth'])->name('user-password.update');
});

// 🌟 削除：便宜的なprofile_editルートを削除し、/mypage/profileのルート名'profile_edit'を優先させます。
// Route::get('/profile', function () {
//     return view('profile.edit'); 
// })->middleware(['auth', 'verified'])->name('profile_edit');