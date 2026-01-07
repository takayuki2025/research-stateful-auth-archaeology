<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        return [
            'shop_code' => 'shop_' . Str::uuid()->toString(),
            'owner_user_id' => null,
            'name' => $this->faker->company,
            'status' => 'active',
        ];
    }
}
