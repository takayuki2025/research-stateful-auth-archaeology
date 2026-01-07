<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
// Domain Repositories
// NOTE: UserRepositoryInterface は App\Domain\Repository\UserRepository に置き換えられていると想定
use App\Domain\Repository\OrderHistoryRepository;
use App\Domain\Repository\CommentRepository;

use App\Domain\Repository\ItemRepositoryInterface;
use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Item\Domain\Repository\ItemRepository;//⚪️
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentItemRepository;//⚪️
use App\Modules\Search\Domain\Repository\ItemSearchRepository;
use App\Modules\Search\Infrastructure\Persistence\Repository\EloquentItemSearchRepository;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use App\Modules\Auth\Infrastructure\Security\JwtTokenVerifier;
use App\Modules\Auth\Domain\Port\UserProvisioningPort;
use App\Modules\User\Application\Service\UserProvisioningService;
// ✅ User モジュール内の Repository Interface を追加
use App\Modules\User\Domain\Repository\ProfileRepository;//⚫️
use App\Modules\User\Domain\Repository\MypageRepository; // ⚫️MypageRepositoryは共通で残す
// Infrastructure Persistence
use App\Infrastructure\Persistence\EloquentUserRepository; // 既存のUserRepository
use App\Modules\User\Infrastructure\Persistence\Repository\EloquentProfileRepository;//⚫️
use App\Infrastructure\Persistence\EloquentOrderHistoryRepository;
use App\Infrastructure\Persistence\EloquentCommentRepository;
use App\Modules\Reaction\Infrastructure\Persistence\EloquentFavoriteRepository;
// use App\Infrastructure\Persistence\EloquentFavoriteRepository;
// use App\Infrastructure\Persistence\EloquentItemRepository;
// ✅ User モジュール内の Repository 実装クラスのパスを修正
use App\Modules\User\Infrastructure\Persistence\Repository\EloquentMypageRepository;// ⚫️MypageRepositoryは共通で残す
// use App\Modules\User\Infrastructure\Persistence\Repository\EloquentProfileRepository;
// Ports
use App\Domain\Payment\StripePaymentPort;
use App\Domain\Auth\FirebaseAuthPort;
// Adapters
use App\Infrastructure\Payment\StripePaymentAdapter;
use App\Infrastructure\Auth\FirebaseAuthAdapter;
use App\Modules\Item\Domain\Repository\ItemEntityTagRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentItemEntityTagRepository;
use App\Modules\Item\Domain\Repository\BrandRepository;
use App\Modules\Item\Infrastructure\Persistence\EntityDefinition\BrandRepositoryImpl;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Shop\Infrastructure\Persistence\EloquentShopRepository;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Order\Infrastructure\Persistence\EloquentOrderRepository;
use App\Modules\Order\Domain\Event\OrderPaid;
use App\Modules\Shipment\Application\Listener\CreateShipmentOnOrderPaidListener;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Clock\SystemClock;
use App\Modules\User\Domain\Repository\UserAddressRepository;
use App\Modules\User\Infrastructure\Persistence\Repository\EloquentUserAddressRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentManagementQueryRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Infrastructure\Persistence\EloquentShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentQueryRepository;
use App\Modules\Shipment\Infrastructure\Persistence\Query\DbShipmentQueryRepository;
use App\Modules\Shop\Domain\Repository\ShopRoleQueryRepository;
use App\Modules\Shop\Infrastructure\Persistence\EloquentShopRoleQueryRepository;
use App\Modules\Order\Domain\Repository\OrderHistoryQueryRepository;
use App\Modules\Order\Infrastructure\Persistence\EloquentOrderHistoryQueryRepository;
use App\Modules\Order\Domain\Repository\OrderQueryRepository;
use App\Modules\Order\Infrastructure\Persistence\EloquentOrderQueryRepository;
use App\Modules\User\Domain\Port\ShopAddressSyncPort;
// この2つはセット：NullShopAddressSyncAdapter＝開発用
use App\Modules\User\Infrastructure\Adapter\ShopAddressSyncAdapter;
use App\Modules\User\Infrastructure\External\NullShopAddressSyncAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {


        $this->app->bind(TokenVerifierPort::class, JwtTokenVerifier::class);

        $this->app->bind(UserProvisioningPort::class, UserProvisioningService::class);

        // Repository binding
        // $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);
        // $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class); // 仮にコメントアウト
        $this->app->bind(OrderHistoryRepository::class, EloquentOrderHistoryRepository::class);
        $this->app->bind(CommentRepository::class, EloquentCommentRepository::class);

        $this->app->bind(
            FavoriteRepository::class,
            EloquentFavoriteRepository::class
        );

        // ✅ ItemRepository は Item モジュール内の Eloquent 版を採用
        // $this->app->bind(ItemRepository::class, EloquentItemRepository::class);

        $this->app->bind(
            ItemSearchRepository::class,
            EloquentItemSearchRepository::class
        );

        // ✅ Mypage は User モジュール内の Eloquent 版を採用
        $this->app->bind(MypageRepository::class, EloquentMypageRepository::class);

        $this->app->bind(ProfileRepository::class, EloquentProfileRepository::class);

        // ✅ ProfileRepository を User モジュール内の実装クラスにバインド
        // $this->app->bind(ProfileRepository::class, EloquentProfileRepository::class);

        // Firebase Auth Port
        $this->app->bind(FirebaseAuthPort::class, function ($app) {
            return new FirebaseAuthAdapter(
                $app->make('firebase.auth')
            );
        });

        // Stripe Payment
        $this->app->bind(StripePaymentPort::class, StripePaymentAdapter::class);

        // ✅ item_entity_tags Write 用
        $this->app->bind(
            ItemEntityTagRepository::class,
            EloquentItemEntityTagRepository::class
        );

        // ✅ Brand 正規化
        $this->app->bind(
            BrandRepository::class,
            BrandRepositoryImpl::class
        );

        $this->app->bind(ShopRepository::class, EloquentShopRepository::class);

        $this->app->bind(
            OrderRepository::class,
            EloquentOrderRepository::class
        );

        $this->app->bind(Clock::class, SystemClock::class);

        $this->app->bind(
            UserAddressRepository::class,
            EloquentUserAddressRepository::class
        );

        $this->app->bind(
            ShipmentManagementQueryRepository::class,
            DbShipmentQueryRepository::class
        );

        $this->app->bind(
            ShipmentRepository::class,
            EloquentShipmentRepository::class
        );

        $this->app->bind(
            ShipmentQueryRepository::class,
            DbShipmentQueryRepository::class
        );

        $this->app->bind(
            ShopRoleQueryRepository::class,
            EloquentShopRoleQueryRepository::class
        );


        $this->app->bind(
            OrderHistoryQueryRepository::class,
            EloquentOrderHistoryQueryRepository::class
        );


        $this->app->bind(
            OrderQueryRepository::class,
            EloquentOrderQueryRepository::class
        );



        if ($this->app->environment('production')) {
            $this->app->bind(
                ShopAddressSyncPort::class,
                ShopAddressSyncAdapter::class
            );
        } else {
            $this->app->bind(
                ShopAddressSyncPort::class,
                NullShopAddressSyncAdapter::class
            );
        }


    }


    public function boot()
    {
        // ここには一切リスナーなど記入しない。
        // Event::listen(
        //     OrderPaid::class,
        //     CreateShipmentOnOrderPaidListener::class
        // );


        // Event::listen(
        //     OrderPaid::class,
        //     OnOrderPaidRecordOrderHistory::class
        // );


    }
}
