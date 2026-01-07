<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    use WithoutMiddleware;


    public function test_create_order(): void
    {
        $user = User::factory()->create();

        // あなたのJWT認証に合わせて調整
        $this->actingAs($user);

        $res = $this->postJson('/api/orders', [
            'shop_id' => 1,
            'items' => [
                [
                    'item_id' => 10,
                    'name' => 'Test Item',
                    'price_amount' => 1000,
                    'price_currency' => 'JPY',
                    'quantity' => 2,
                    'condition' => '美品',
                    'category' => ['camera'],
                    'image_path' => 'item_images/x.png',
                ]
            ],
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure(['order_id', 'status', 'total_amount', 'currency']);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shop_id' => 1,
            'status'  => 'pending_payment',
            'total_amount' => 2000,
            'currency' => 'JPY',
        ]);
    }
}
