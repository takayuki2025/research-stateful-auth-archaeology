<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use Illuminate\Support\Facades\DB;

final class StartPaymentTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_start_payment_card_creates_payment(): void
    {
        $this->app->bind(PaymentGatewayPort::class, function () {
            return new class () implements PaymentGatewayPort {
                public function start(PaymentMethod $method, int $amount, string $currency, array $context): array
                {
                    return [
                        'provider_payment_id' => 'pi_test_123',
                        'client_secret' => 'cs_test_123',
                        'requires_action' => false,
                        'status' => 'requires_payment_method',
                    ];
                }
                public function parseWebhook(string $payload, string $signature): array
                {
                    return [];
                }
            };
        });

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create Order (minimum row)
        $orderId = DB::table('orders')->insertGetId([
            'shop_id' => 1,
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'total_amount' => 2000,
            'currency' => 'JPY',
            'items_snapshot' => json_encode([['item_id' => 1,'name' => 'x','price_amount' => 2000,'price_currency' => 'JPY','quantity' => 1]]),
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->postJson('/api/payments/start', [
            'order_id' => $orderId,
            'method' => 'card',
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure(['payment_id','status','provider_payment_id','client_secret','instructions']);

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'provider' => 'stripe',
            'method' => 'card',
            'provider_payment_id' => 'pi_test_123',
        ]);
    }
}
