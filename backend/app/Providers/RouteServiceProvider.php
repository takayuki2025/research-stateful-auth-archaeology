<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify; // ★ Fortifyをインポート

class RouteServiceProvider extends ServiceProvider
{


    /**
     * ★ 修正: メール認証後のリダイレクト先をFRONTEND_URLに設定 ★
     * メール認証成功後は、Next.jsのトップページへ戻します。
     * この値は、認証コントローラーなどでリダイレクトURIとして使用されます。
     */
    public const HOME = '/'; // ルートパスに変更



    /**
     * アプリケーションのルートマッピングを定義します。
     *
     * @return void
     */
    public function register(): void
    {
        // ★★★ 修正箇所: Fortify::routes()の呼び出しを削除 ★★★
        /* 削除したブロック
        if (!$this->app->runningInConsole()) {
            Fortify::routes();
        }
        */
        // -------------------------------------------------------------
    }

    /**
     * アプリケーションのルートマッピングを定義します。
     */
    public function boot(): void
    {
        $this->routes(function () {

            // WebルートはFortifyのルート登録後にrequire base_path('routes/web.php')でロードされます。
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
