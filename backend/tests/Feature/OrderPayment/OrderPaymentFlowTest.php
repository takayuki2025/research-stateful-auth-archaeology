<?php

namespace Tests\Feature\OrderPayment;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Application\UseCase\HandlePaymentWebhookUseCase;
use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;

final class OrderPaymentFlowTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_order_payment_flow_creates_shipment(): void
    {
        /* =====================================================
           1) PaymentGateway Fake
        ===================================================== */
        $this->app->bind(PaymentGatewayPort::class, function () {
            return new class () implements PaymentGatewayPort {
                public function start(
                    PaymentMethod $method,
                    int $amount,
                    string $currency,
                    array $context
                ): array {
                    return [
                        'provider_payment_id' => 'pi_test_flow_123',
                        'client_secret' => 'cs_test_flow_123',
                        'requires_action' => false,
                        'status' => 'requires_payment_method',
                    ];
                }

                public function createPaymentIntent(
                    PaymentMethod $method,
                    array $payload
                ): array {
                    return [
                        'payment_intent_id' => 'pi_test_flow_123',
                        'client_secret' => 'cs_test_flow_123',
                    ];
                }

                public function parseWebhook(string $payload, string $signature): array
                {
                    return [];
                }
            };
        });

        /* =====================================================
           2) User / Order 作成
        ===================================================== */
        $user = User::factory()->create();
        $this->actingAs($user);

        $orderId = DB::table('orders')->insertGetId([
            'shop_id' => 1,
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'total_amount' => 2000,
            'currency' => 'JPY',
            'items_snapshot' => json_encode([
                [
                    'item_id' => 1,
                    'name' => 'Sample',
                    'price_amount' => 2000,
                    'price_currency' => 'JPY',
                    'quantity' => 1,
                ]
            ], JSON_UNESCAPED_UNICODE),
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /* =====================================================
           3) ★ Address 確定（ここが重要）
        ===================================================== */
        DB::table('orders')->where('id', $orderId)->update([
            'address_snapshot' => json_encode([
                'postal_code' => '100-0001',
                'prefecture' => '東京都',
                'city' => '千代田区',
                'address_line1' => '千代田1-1',
                'address_line2' => null,
                'recipient_name' => '山田 太郎',
                'phone' => '09000000000',
            ], JSON_UNESCAPED_UNICODE),
            'address_confirmed_at' => now(),
        ]);

        /* =====================================================
           4) Payment Start
        ===================================================== */
        $paymentRes = $this->postJson('/api/payments/start', [
            'order_id' => $orderId,
            'method' => 'card',
        ]);

        $paymentRes->assertStatus(200);

        $paymentId = (int) $paymentRes->json('payment_id');

        /* =====================================================
           5) Webhook（payment_intent.succeeded）
        ===================================================== */
        $webhook = app(HandlePaymentWebhookUseCase::class);

        $payload = [
            'id' => 'evt_test_flow_001',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_flow_123',
                ],
            ],
        ];

        $input = new HandlePaymentWebhookInput(
            provider: 'stripe',
            eventId: 'evt_test_flow_001',
            eventType: 'payment_intent.succeeded',
            payload: $payload,
            payloadHash: hash('sha256', json_encode($payload)),
            occurredAt: new \DateTimeImmutable(),
        );

        $webhook->handle($input);

        /* =====================================================
           6) 検証
        ===================================================== */

        // Order → paid
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'paid',
        ]);

        // Payment → succeeded
        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'status' => 'succeeded',
        ]);

        // ★ Shipment が作成されている
        $this->assertDatabaseHas('shipments', [
            'order_id' => $orderId,
            'shop_id' => 1,
        ]);
    }
}
