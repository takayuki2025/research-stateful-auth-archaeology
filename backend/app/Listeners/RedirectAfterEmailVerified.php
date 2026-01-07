<?php

namespace App\Listeners;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RedirectAfterEmailVerified
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \IlluminateAuthEventsVerified  $event
     * @return void
     */
    public function handle(Verified $event): void
    {
        // Next.js のプロフィール設定ページへ移動させる
        $frontendUrl = config('app.frontend_url') . '/mypage/profile?verified=true';

        // ブラウザリダイレクトではなく、コントローラで return redirect() させるために
        // ここでは URL 文字列だけをセットしておけば十分
        session(['redirect_after_verify' => $frontendUrl]);
    }
}
