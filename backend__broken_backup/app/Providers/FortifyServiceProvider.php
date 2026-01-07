<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Route; 

// カスタムForm Requestのインポート（今回はFortifyのDIは使わないが、念のため残しておく）
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

use App\Http\Responses\VerifyEmailResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

// Fortifyが使用するデフォルトのForm Requestのインポートを復活（DIバインドは削除）
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Http\Requests\RegisterRequest as FortifyRegisterRequest;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fortifyのメール認証完了後のリダイレクトをカスタマイズ
        $this->app->singleton(
            VerifyEmailResponseContract::class,
            VerifyEmailResponse::class
        );
        
        // DIバインドは不要または動作しないため削除し、元の状態に戻します。
        /*
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        $this->app->bind(FortifyRegisterRequest::class, RegisterRequest::class);
        */
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ★★★ 修正箇所: manualFortifyRoutes()の呼び出しを削除する ★★★
        // $this->manualFortifyRoutes(); 
        // -----------------------------------------------------------

        // Fortify::register()の呼び出しはエラーのため削除されたまま

        Fortify::createUsersUsing(CreateNewUser::class);
        
        // クロージャに Request $request 引数を追加済み
        Fortify::registerView(function (Request $request) {
            return view('auth.register');
        });

        // クロージャに Request $request 引数を追加済み
        Fortify::loginView(function (Request $request) {
            return view('auth.login');
        });
        
        // パスワードリセット関連ビューに Request $request 引数を追加済み
        Fortify::requestPasswordResetLinkView(function (Request $request) {
            return view('auth.forgot-password', ['request' => $request]);
        });
        
        Fortify::resetPasswordView(function (Request $request, $token) {
            return view('auth.reset-password', ['request' => $request, 'token' => $token]);
        });
        

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(50)->by($email . $request->ip());
        });

        // ログイン後のリダイレクトを /onetime に集約
        Fortify::redirects('login', function () {
            return route('onetime.show');
        });

        // メール認証完了後のリダイレクトを /onetime に集約
        Fortify::redirects('verification', function () {
            return route('onetime.show');
        });

        // プロフィール更新後のリダイレクトを /onetime に集約
        Fortify::redirects('user-profile-information', function () {
            return route('onetime.show');
        });

        // パスワードリセット後のリダイレクトを /onetime に集約
        Fortify::redirects('password-reset', function () {
            return route('onetime.show');
        });

        // verifyEmailViewのクロージャに Request $request 引数を追加済み
        Fortify::verifyEmailView(function (Request $request) {
            return view('email_check');
        });
    }

    /**
     * manualFortifyRoutes() メソッドは削除されました。
     * Fortifyのルートは使用せず、web.phpで手動で定義します。
     */
}
