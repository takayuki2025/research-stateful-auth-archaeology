<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



/*
|--------------------------------------------------------------------------
| Auth API (SPA + Sanctum)　api.php
|--------------------------------------------------------------------------
*/

use App\Modules\Auth\Presentation\Http\Controllers\MeController;
use App\Modules\Auth\Presentation\Http\Controllers\ConfirmFirstLoginController;
use App\Modules\Auth\Presentation\Http\Controllers\RegisterController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);
    Route::post('/auth/first-login', ConfirmFirstLoginController::class);
});

Route::post('/register', RegisterController::class);


use App\Modules\Auth\Presentation\Http\Controllers\VerifyEmailController;

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed'])
    ->name('verification.verify');


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
| ④ Shop / Tenant
|--------------------------------------------------------------------------
*/
use App\Modules\Shop\Presentation\Http\Controllers\ShopController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/shops', [ShopController::class, 'create']);
    Route::get('/shops/me', [ShopController::class, 'me']);
});

// Public Shop
use App\Modules\Item\Presentation\Http\Controllers\{
    ShopShowController,
    ShopItemListController
};

Route::prefix('shops/{shop_code}')
    ->middleware('shop.context')
    ->group(function () {
        Route::get('/', ShopShowController::class);
        Route::get('/items', ShopItemListController::class);
    });

/*
|--------------------------------------------------------------------------
| ⑤ Shop Dashboard / Management
|--------------------------------------------------------------------------
*/
use App\Modules\Order\Presentation\Http\Controllers\ShopOrderListController;
use App\Modules\Order\Presentation\Http\Controllers\ShopOrderShipmentController;
use App\Modules\Shipment\Presentation\Http\Controllers\{
    ShopShipmentListController,
    ShipmentController
};

Route::prefix('shops/{shop_code}')
    ->middleware(['auth:sanctum', 'shop.context'])
    ->group(function () {

        Route::get('/dashboard/orders', ShopOrderListController::class);

        Route::get(
            '/dashboard/orders/{orderId}/shipment',
            ShopOrderShipmentController::class
        );

        Route::post(
            '/dashboard/orders/{orderId}/shipment',
            [ShipmentController::class, 'store']
        );

        Route::get('/shipments', ShopShipmentListController::class);
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
| ⑦ Item Draft / Publish
|--------------------------------------------------------------------------
*/
use App\Modules\Item\Presentation\Http\Controllers\{
    CreateItemDraftController,
    UploadItemDraftImageController,
    PublishItemController,
    DeleteItemDraftController
};

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/items/drafts', CreateItemDraftController::class);
    Route::post('/items/drafts/{draftId}/image', UploadItemDraftImageController::class);
    Route::post('/items/drafts/{draftId}/publish', PublishItemController::class);
    Route::delete('/items/drafts/{draftId}', DeleteItemDraftController::class);
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



/*
|--------------------------------------------------------------------------
| ⑨ Order
|--------------------------------------------------------------------------
*/
use App\Modules\Order\Presentation\Http\Controllers\{
    OrderController,
    ConfirmOrderController,
    MeOrderController,
    OrderReadController,
    GetMyOrderShipmentController
};

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [OrderController::class, 'create']);
    Route::post('/orders/{orderId}/address', [OrderController::class, 'confirmAddress']);
    Route::get('/orders/{orderId}', [OrderController::class, 'detail']);
    Route::post('/orders/{orderId}/confirm', ConfirmOrderController::class);

    Route::get('/me/orders', [MeOrderController::class, 'index']);
    Route::get('/me/orders/{orderId}', [OrderReadController::class, 'show']);
    Route::get('/me/orders/{orderId}/shipment', GetMyOrderShipmentController::class);
});

/*
|--------------------------------------------------------------------------
| ⑩ Payment
|--------------------------------------------------------------------------
*/
use App\Modules\Payment\Presentation\Http\Controllers\{
    PaymentController,
    StripeWebhookController,
    PaymentReadController
};

Route::middleware('auth:sanctum')->post('/payments/start', [PaymentController::class, 'start']);
Route::post('/webhooks/stripe', StripeWebhookController::class);
Route::get('/payments/latest-by-order', [PaymentReadController::class, 'latestByOrder']);

/*
|--------------------------------------------------------------------------
| ⑪ Shipment
|--------------------------------------------------------------------------
*/
use App\Modules\Shipment\Presentation\Http\Controllers\{
    PackShipmentController,
    ShipShipmentController,
    InTransitShipmentController,
    DeliverShipmentController,
    AdminShipmentKpiController,
    CustomerShipmentController
};

Route::middleware('auth:sanctum')->prefix('shipments/{shipmentId}')->group(function () {
    Route::post('pack', PackShipmentController::class);
    Route::post('ship', ShipShipmentController::class);
    Route::post('in-transit', InTransitShipmentController::class);
    Route::post('deliver', DeliverShipmentController::class);
});

Route::get('/admin/shipments/kpi', AdminShipmentKpiController::class)
    ->middleware(['auth:sanctum', 'role:admin']);

Route::get('/me/shipments/{id}', [CustomerShipmentController::class, 'show'])
    ->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| ⑫ Analytics / Entity Processing
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\{
    ItemEntityReviewController,
    ItemEntityAuditController,
    EntityKpiController
};

Route::get('/entity-reviews', [ItemEntityReviewController::class, 'index']);
Route::post('/entity-reviews/{id}/approve', [ItemEntityReviewController::class, 'approve']);
Route::post('/entity-reviews/{id}/reject', [ItemEntityReviewController::class, 'reject']);
Route::get('/item-entities/{id}/audits', [ItemEntityAuditController::class, 'index']);
Route::get('/entity-kpis', EntityKpiController::class);

/*
|--------------------------------------------------------------------------
| ⑬ User Address
|--------------------------------------------------------------------------
*/
use App\Modules\User\Presentation\Http\Controllers\UserAddressController;

Route::middleware('auth:sanctum')
    ->get('/me/addresses/primary', [UserAddressController::class, 'primary']);



    // アトラスカーネル解析結果。
    use App\Modules\Review\Presentation\Http\Controllers\ReviewController;

Route::prefix('review')->group(function () {
    Route::get('/items', [ReviewController::class, 'list']);
    Route::get('/items/{itemId}', [ReviewController::class, 'show']);

    Route::post('/items/{itemId}/confirm', [ReviewController::class, 'confirm']);
    Route::post('/items/{itemId}/edit-confirm', [ReviewController::class, 'editConfirm']);
    Route::post('/items/{itemId}/reject', [ReviewController::class, 'reject']);
});