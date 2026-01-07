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
    public function handle(Verified $event)
    {
        // 認証後にprofile.editページにリダイレクト
        return redirect()->route('profile_edit');
    }
}
