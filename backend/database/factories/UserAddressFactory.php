<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    protected $model = UserAddress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'post_number' => '232-1355',
            'prefecture' => '千葉県',
            'city' => '千葉',
            'address_line1' => 'ハイツB',
            'address_line2' => null,

            'recipient_name' => 'テスト用のユーザ',
            'phone' => null,

            'is_primary' => true,
        ];
    }

    /**
     * primary 住所だけ作りたい場合用
     */
    public function primary(): static
    {
        return $this->state([
            'is_primary' => true,
        ]);
    }
}
