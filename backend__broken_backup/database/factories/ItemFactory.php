<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\User;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(), // 関連するユーザーを自動作成
            'name' => $this->faker->word,
            'price' => $this->faker->numberBetween(100, 10000),
            'brand' => $this->faker->company,
            'explain' => $this->faker->sentence,
            'condition' => $this->faker->randomElement(['新品、未使用', '未使用に近い', '目立った傷や汚れなし', 'やや傷や汚れあり', '傷や汚れあり', '全体的に状態が悪い']),
            'category' => [$this->faker->word, $this->faker->word],
            'item_image' => 'https://placehold.co/600x400/000000/FFFFFF.png?text=Item_Image',
            'remain' => $this->faker->boolean,
        ];
    }
}