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





// use App\Modules\Item\Presentation\Http\Controllers\PublicCatalogController;

// Route::get('/items/public', PublicCatalogController::class);




/*
|--------------------------------------------------------------------------
| ③ User / MyPage
|--------------------------------------------------------------------------
*/
use App\Modules\User\Presentation\Http\Controllers\MypageController;
use App\Modules\Order\Presentation\Http\Controllers\MyPageBoughtController;

Route::middleware('auth:sanctum')
    ->prefix('mypage')
    ->group(function () {

        // Profile
        Route::get('/profile', [MypageController::class, 'profile']);
        Route::post('/profile', [MypageController::class, 'createProfile']);
        Route::patch('/profile', [MypageController::class, 'updateProfile']);
        Route::post('/profile/image', [MypageController::class, 'updateProfileImage']);

        // MyPage
        Route::get('/sell', [MypageController::class, 'sellItems']);
        Route::get('/bought', MyPageBoughtController::class);
    });





/*
|--------------------------------------------------------------------------
| ⑥ Item / Public Catalog & Search
|--------------------------------------------------------------------------
*/
use App\Modules\Item\Presentation\Http\Controllers\{
    ItemDetailController,
    PublicCatalogController
};
use App\Modules\Search\Presentation\Http\Controllers\{
    PublicItemSearchController,
    ShopItemSearchController
};

// 🌐 完全 Public（ログイン不要）
Route::get('/items/public', PublicCatalogController::class);
Route::get('/search/items', PublicItemSearchController::class);
Route::get('/search/shop-items', ShopItemSearchController::class);
// Route::get('/item/{id}', ItemDetailController::class);
Route::get('/items/{itemId}', ItemDetailController::class)
    ->whereNumber('itemId');

Route::middleware('auth.sanctum.optional')->group(function () {
    Route::get('/items/{itemId}', ItemDetailController::class)->whereNumber('itemId');
});




    /*
|--------------------------------------------------------------------------
| ⑧ Reaction / Comment
|--------------------------------------------------------------------------
*/
use App\Modules\Reaction\Presentation\Http\Controllers\FavoriteController;
use App\Modules\Comment\Presentation\Http\Controllers\PostCommentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/items/favorite', [FavoriteController::class, 'index']);
    Route::post('/reactions/items/{itemId}/favorite', [FavoriteController::class, 'add']);
    Route::delete('/reactions/items/{itemId}/favorite', [FavoriteController::class, 'remove']);
    Route::post('/comment', PostCommentController::class);
});