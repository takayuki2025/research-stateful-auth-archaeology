<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Shop;
use App\Policies\AtlasReviewPolicy;
// use App\Auth\Guards\FirebaseGuard; // ★ 追加: 作成したカスタムGuardをインポート
// use Illuminate\Support\Facades\Gate;
// use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Illuminate\Support\Facades\Auth;
// use Kreait\Firebase\Contract\Auth as FirebaseAuth;
// use Illuminate\Http\Request; // ★ 削除: use文があってもエラーになるため、完全修飾名を使用します
// use App\Auth\UserResolver;
// use App\Auth\JwtUserResolver;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Shop::class => AtlasReviewPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
