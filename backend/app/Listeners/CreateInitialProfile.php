<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Repository\ProfileRepository;

final class CreateInitialProfile
{
    public function __construct(
        private ProfileRepository $profiles,
    ) {}

    public function handle(Registered $event): void
    {
        $user = $event->user;

        // 既に profile があれば何もしない
        if ($this->profiles->findByUserId($user->id)) {
            return;
        }

        // 🔑 display_name だけ入れる
        $profile = Profile::createEmpty(
            userId: $user->id,
            displayName: $user->name
        );

        $this->profiles->save($profile);
    }
}