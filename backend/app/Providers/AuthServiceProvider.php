<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ★★★ 修正箇所: カスタムGuardをファクトリとして使用 ★★★
        // Auth::extend('firebase', function ($app, $name, array $config) {

        //     $userProvider = Auth::createUserProvider($config['provider'] ?? null);
        //     $firebaseAuth = $app->make(FirebaseAuth::class);

        //     // ★ 修正: Requestクラスの完全修飾名を使用 (\Illuminate\Http\Request::class)
        //     $request = $app->make(\Illuminate\Http\Request::class);

        //     // カスタム作成したFirebaseGuardのインスタンスを生成して返す
        //     return new FirebaseGuard($userProvider, $firebaseAuth, $request);
        // });
    }
}
