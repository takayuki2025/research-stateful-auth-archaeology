<?php

namespace App\Modules\User\Infrastructure\Persistence\Repository;

use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Repository\ProfileRepository;
use Illuminate\Support\Facades\DB;

final class EloquentProfileRepository implements ProfileRepository
{
    public function findByUserId(int $userId): ?Profile
    {
        $row = DB::table('profiles')
            ->where('user_id', $userId)
            ->first();

        if (! $row) {
            return null;
        }

        return Profile::reconstitute(
            userId: (int)$row->user_id,
            displayName: (string)$row->display_name,
            postNumber: $row->post_number,
            address: $row->address,
            building: $row->building,
            userImage: $row->user_image,
        );
    }

    public function save(Profile $profile): Profile
    {

        \Log::warning('[🔥PROFILE_SAVE]', [
        'user_id' => $profile->userId(),
        'display_name' => $profile->displayName(),
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
    ]);

        DB::table('profiles')->updateOrInsert(
            ['user_id' => $profile->userId()],
            [
                'display_name' => $profile->displayName(),
                'post_number'  => $profile->postNumber(),
                'address'      => $profile->address(),
                'building'     => $profile->building(),
                'user_image'   => $profile->userImage(),
                'updated_at'   => now(),
            ]
        );

        return $profile;
    }
}
