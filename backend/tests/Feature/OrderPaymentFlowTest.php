<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_address_then_start_payment(): void
    {
        $user = User::factory()->create();

        $address = UserAddress::factory()->create([
            'user_id' => $user->id,
            'is_primary' => true,
        ]);

        // ★ testing 環境では guard 指定しない
        $this->actingAs($user);

        // Order 作成
        $orderRes = $this->postJson('/api/orders', [
            'shop_id' => 1,
            'items' => [
                [
                    'item_id' => 1,
                    'name' => 'Test Item',
                    'price_amount' => 1000,
                    'price_currency' => 'JPY',
                    'quantity' => 1,
                ],
            ],
        ]);

        $orderRes->assertStatus(200);

        $orderId = $orderRes->json('order_id');

        // Address 確定
        $this->postJson("/api/orders/{$orderId}/confirm-address", [
            'address_id' => $address->id,
        ])->assertStatus(200);

        // Payment Start
        $this->postJson('/api/payments/start', [
            'order_id' => $orderId,
            'method' => 'card',
        ])->assertStatus(200);
    }
}
