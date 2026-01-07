<?php

namespace App\Modules\User\Infrastructure\Persistence\Repository;

use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Entity\UserAddress;
use App\Modules\User\Domain\Repository\UserAddressRepository;
use App\Models\UserAddress as UserAddressModel;

final class EloquentUserAddressRepository implements UserAddressRepository
{
    public function findPrimaryByUser(int $userId): ?UserAddress
    {
        $model = UserAddressModel::where('user_id', $userId)
            ->where('is_primary', true)
            ->first();

        if (! $model) {
            return null;
        }

        return $this->toEntity($model);
    }

    /**
     * Profile から primary address を 1 件生成（暫定）
     */
    public function createPrimaryFromProfile(
        int $userId,
        Profile $profile
    ): UserAddress {

        $model = UserAddressModel::create([
            'user_id'        => $userId,

            // ✅ ここが今回の修正点
            'post_number'    => $profile->postNumber(),   // ★ 必須

            // 暫定値（後で住所編集で更新）
            'prefecture'     => '未設定',
            'city'           => '未設定',

            'address_line1'  => $profile->address(),
            'address_line2'  => $profile->building(),
            'recipient_name' => $profile->displayName(),
            'phone'          => null,

            'is_primary'     => true,
        ]);

        return $this->toEntity($model);
    }

    private function toEntity(UserAddressModel $model): UserAddress
    {
        return new UserAddress(
            id: $model->id,
            userId: $model->user_id,
            postalCode: $model->post_number,
            prefecture: $model->prefecture,
            city: $model->city,
            addressLine1: $model->address_line1,
            addressLine2: $model->address_line2,
            recipientName: $model->recipient_name,
            phone: $model->phone,
            isPrimary: $model->is_primary,
        );
    }
}
