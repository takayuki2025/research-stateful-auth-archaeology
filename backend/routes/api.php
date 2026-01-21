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

Route::middleware('auth.occ')->group(function () {
    Route::get('/me', MeController::class);
    Route::post('/auth/first-login', ConfirmFirstLoginController::class);
});

Route::post('/register', RegisterController::class);


use App\Modules\Auth\Presentation\Http\Controllers\VerifyEmailController;

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed'])
    ->name('verification.verify');



// 認証システム
use App\Modules\Auth\Presentation\Http\Controllers\DevIssueJwtController;
Route::middleware(['admin.fixed_or_key'])
    ->post('/dev/jwt', DevIssueJwtController::class);

    // use Illuminate\Http\Request;

Route::get('/__debug/authz', function (Request $request) {
    return response()->json([
        'authorization' => $request->header('Authorization'),
        'has_authz' => $request->headers->has('Authorization'),
    ]);
});

use App\Modules\Auth\Presentation\Http\Controllers\FirebaseAuthController;

Route::post('/login_or_register', [FirebaseAuthController::class, 'loginOrRegister']);
/*
|--------------------------------------------------------------------------
| ③ User / MyPage
|--------------------------------------------------------------------------
*/
use App\Modules\User\Presentation\Http\Controllers\MypageController;
use App\Modules\Order\Presentation\Http\Controllers\MyPageBoughtController;

Route::middleware('auth.occ')
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

Route::middleware('auth.occ')->group(function () {
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
    ->middleware(['auth.occ', 'shop.context'])
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

Route::middleware('auth.occ')->group(function () {
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

Route::middleware('auth.occ')->group(function () {
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

Route::middleware('auth.occ')->group(function () {
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

Route::middleware('auth.occ')->post('/payments/start', [PaymentController::class, 'start']);
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

Route::middleware('auth.occ')->prefix('shipments/{shipmentId}')->group(function () {
    Route::post('pack', PackShipmentController::class);
    Route::post('ship', ShipShipmentController::class);
    Route::post('in-transit', InTransitShipmentController::class);
    Route::post('deliver', DeliverShipmentController::class);
});

Route::get('/admin/shipments/kpi', AdminShipmentKpiController::class)
    ->middleware(['auth.occ', 'role:admin']);

Route::get('/me/shipments/{id}', [CustomerShipmentController::class, 'show'])
    ->middleware('auth.occ');

/*
|--------------------------------------------------------------------------
| ⑫ Analytics / Entity Processing
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\{
    // ItemEntityReviewController,
    ItemEntityAuditController,
    EntityKpiController
};

// Route::get('/entity-reviews', [ItemEntityReviewController::class, 'index']);
// Route::post('/entity-reviews/{id}/approve', [ItemEntityReviewController::class, 'approve']);
// Route::post('/entity-reviews/{id}/reject', [ItemEntityReviewController::class, 'reject']);
Route::get('/item-entities/{id}/audits', [ItemEntityAuditController::class, 'index']);
Route::get('/entity-kpis', EntityKpiController::class);

/*
|--------------------------------------------------------------------------
| ⑬ User Address
|--------------------------------------------------------------------------
*/
use App\Modules\User\Presentation\Http\Controllers\UserAddressController;

Route::middleware('auth.occ')
    ->get('/me/addresses/primary', [UserAddressController::class, 'primary']);



    //１４・ アトラスカーネル解析結果。
    use App\Modules\Review\Presentation\Http\Controllers\ReviewController;

Route::prefix('review')->group(function () {
    Route::get('/items', [ReviewController::class, 'list']);
    Route::get('/items/{itemId}', [ReviewController::class, 'show']);

    Route::post('/items/{itemId}/confirm', [ReviewController::class, 'confirm']);
    Route::post('/items/{itemId}/edit-confirm', [ReviewController::class, 'editConfirm']);
    Route::post('/items/{itemId}/reject', [ReviewController::class, 'reject']);
});

// AI評価数値のUI化
use App\Modules\Item\Presentation\Http\Controllers\GetItemAnalysisResultController;

Route::get(
    '/items/{itemId}/analysis',
    GetItemAnalysisResultController::class
);

// 解析結果後の処理前
use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\GetItemAnalysisForReviewController;

Route::get(
    '/items/{itemId}/analysis/review',
    GetItemAnalysisForReviewController::class
);


// 再解析処理ボタン
use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\ReplayAnalysisController;


Route::middleware(['auth.occ', 'can:atlas-review'])
    ->post('/atlas/requests/{id}/replay', ReplayAnalysisController::class);




use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasRequestShowController;

Route::get(
  '/shops/{shop_code}/atlas/requests/{request_id}',
  AtlasRequestShowController::class
);




use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasRequestDecideController;

Route::middleware(['auth.occ'])->group(function () {

    // Aルート：Approve/Reject
    Route::post('/shops/{shop_code}/atlas/requests/{request_id}/decide', AtlasRequestDecideController::class);
});




// v3 Controllers
use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasRequestController;
use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasReviewController;
use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasReplayController;

Route::middleware(['auth.occ'])
    ->prefix('shops/{shop_code}/atlas')
    ->group(function () {

        // 1) 一覧（Review一覧ページが叩く）
        Route::get('requests', [AtlasRequestController::class, 'index']);

        // 2) Review（Before/After/Diff 取得）
        Route::get(
    'requests/{request_id}/review',
    [AtlasReviewController::class, 'show']
)->whereNumber('request_id');

        // 6) Replay（非同期再解析）
        Route::post('requests/{request_id}/replay', [AtlasReplayController::class, 'replay'])
            ->whereNumber('request_id');
    });



use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasDecisionHistoryController;

Route::prefix('shops/{shop_code}/atlas')->group(function () {

    // 🟢 History 一覧
    Route::get(
        'history',
        [AtlasDecisionHistoryController::class, 'index']
    );

    // 🟢 History 詳細
    Route::get(
        'requests/{requestId}/history',
        [AtlasDecisionHistoryController::class, 'history']
    );

    // 🟡 Replay（Dフェーズ）
    // Route::post(
    //     'requests/{requestId}/replay',
    //     [AtlasDecisionHistoryController::class, 'replay']
    // );
});





use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasResolveController;
use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\AtlasDecideController;

Route::prefix('shops/{shop_code}/atlas/requests/{request_id}')
    ->middleware(['auth.occ', 'shop.context'])
    ->group(function () {
        Route::post('/resolve', AtlasResolveController::class);
        Route::post('/decide',  AtlasDecideController::class);
    });





use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\EditConfirmDBController;

Route::middleware(['auth.occ'])
    ->prefix('shops/{shop_code}/atlas/requests/{request_id}')
    ->group(function () {
        Route::post(
            'decide',
            [EditConfirmDBController::class, 'decide']
        );
    });



use App\Modules\Item\Presentation\Http\Controllers\AtlasKernel\EditConfirmSelectController;

Route::middleware(['auth.occ'])
    ->prefix('entities')
    ->group(function () {
        Route::get('brands',     [EditConfirmSelectController::class, 'brands']);
        Route::get('conditions', [EditConfirmSelectController::class, 'conditions']);
        Route::get('colors',     [EditConfirmSelectController::class, 'colors']);
    });


// トラストレジャーペイメントシステム
use App\Modules\Payment\Presentation\Http\Controllers\Wallet\ListPaymentMethodsController;

Route::middleware('auth.occ')
    ->prefix('wallet')
    ->group(function () {
        Route::get('payment-methods', ListPaymentMethodsController::class);
    });


use App\Modules\Payment\Presentation\Http\Controllers\Wallet\CreateSetupIntentController;

Route::middleware('auth.occ')
    ->prefix('wallet')
    ->group(function () {
        Route::post('setup-intent', CreateSetupIntentController::class);
    });


use App\Modules\Payment\Presentation\Http\Controllers\Wallet\SetDefaultPaymentMethodController;
use App\Modules\Payment\Presentation\Http\Controllers\Wallet\DetachPaymentMethodController;

Route::middleware('auth.occ')
    ->prefix('wallet')
    ->group(function () {
        Route::post('payment-methods/{id}/default', SetDefaultPaymentMethodController::class);
        Route::delete('payment-methods/{id}', DetachPaymentMethodController::class);
    });


use App\Modules\Payment\Presentation\Http\Controllers\Wallet\OneClickCheckoutController;

Route::middleware('auth.occ')
    ->prefix('wallet')
    ->group(function () {
        Route::post('one-click-checkout', OneClickCheckoutController::class);
    });


use App\Modules\Payment\Presentation\Http\Controllers\Ledger\GetLedgerSummaryController;
use App\Modules\Payment\Presentation\Http\Controllers\Ledger\GetLedgerEntriesController;

Route::middleware('auth.occ')->group(function () {
    Route::get('/ledger/summary', GetLedgerSummaryController::class);
    Route::get('/ledger/entries', GetLedgerEntriesController::class);
});


use App\Modules\Payment\Presentation\Http\Controllers\Ledger\GetLedgerReconciliationController;
use App\Modules\Payment\Presentation\Http\Controllers\Ledger\ReplaySalePostingController;

Route::middleware('auth.occ')->group(function () {
    Route::get('/ledger/reconciliation', GetLedgerReconciliationController::class);
    Route::post('/ledger/replay/sale', ReplaySalePostingController::class);
});


use App\Modules\Payment\Presentation\Http\Controllers\Accounts\GetBalanceController;
use App\Modules\Payment\Presentation\Http\Controllers\Accounts\RecalculateBalanceController;

Route::middleware('auth.occ')->group(function () {
    Route::get('/accounts/{accountId}/balance', GetBalanceController::class);
    Route::post('/shops/{shopId}/balance/recalculate', RecalculateBalanceController::class);
});


use App\Modules\Payment\Presentation\Http\Controllers\Accounts\CreateHoldController;
use App\Modules\Payment\Presentation\Http\Controllers\Accounts\ReleaseHoldController;

Route::middleware('auth.occ')->group(function () {
    Route::post('/accounts/{accountId}/holds', CreateHoldController::class);
    Route::post('/holds/{holdId}/release', ReleaseHoldController::class);
});


use App\Modules\Payment\Presentation\Http\Controllers\Accounts\RequestPayoutController;
use App\Modules\Payment\Presentation\Http\Controllers\Accounts\MarkPayoutStatusController;

Route::middleware('auth.occ')->group(function () {
    Route::post('/accounts/{accountId}/payouts', RequestPayoutController::class);
    Route::post('/payouts/{payoutId}/status', MarkPayoutStatusController::class);
});


// Ruby
use App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger\{
    GetGlobalKpiController,
    GetShopKpisController,
    SearchPostingsController,
    GetPostingDetailController,
    ListMissingSalesController,
    ReplaySaleController,
    ListWebhookEventsController,
    ListHoldsController,
    ListPayoutsController,
    MarkPayoutStatusAdminController,
    AdminRecalculateShopBalanceController
};

use App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger\GetWebhookEventDetailController;
use App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger\ReplayWebhookEventController;

Route::middleware(['admin.fixed_or_key'])
    ->prefix('admin/trustledger')
    ->group(function () {

    Route::get('health', fn () => response()->json([
            'ok' => true,
            'service' => 'trustledger-admin',
            'time' => now()->toIso8601String(),
        ]));

        Route::get('kpis/global', GetGlobalKpiController::class);
        Route::get('kpis/shops', GetShopKpisController::class);

        Route::get('postings', SearchPostingsController::class);
        Route::get('postings/{postingId}', GetPostingDetailController::class)->whereNumber('postingId');

        Route::get('reconciliation/missing-sales', ListMissingSalesController::class);
        Route::post('replay/sale', ReplaySaleController::class);

        Route::get('webhooks/events', ListWebhookEventsController::class);

        Route::get('holds', ListHoldsController::class);
        Route::get('payouts', ListPayoutsController::class);
        Route::post('payouts/{payoutId}/status', MarkPayoutStatusAdminController::class)->whereNumber('payoutId');

        Route::post('shops/{shopId}/balance/recalculate', AdminRecalculateShopBalanceController::class)->whereNumber('shopId');

        Route::get('webhooks/events/{eventId}', GetWebhookEventDetailController::class);
        Route::post('webhooks/events/{eventId}/replay', ReplayWebhookEventController::class);
    });