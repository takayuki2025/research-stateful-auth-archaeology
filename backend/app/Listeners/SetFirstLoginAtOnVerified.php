<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;

class SetFirstLoginAt
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user->first_login_at === null) {
            $user->forceFill([
                'first_login_at' => now(),
            ])->save();
        }
    }
}