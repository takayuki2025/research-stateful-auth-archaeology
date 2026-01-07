<?php

namespace App\Modules\Payment\Infrastructure\External;

use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use Stripe\StripeClient;
use Stripe\Webhook;

final class StripePaymentGateway implements PaymentGatewayPort
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient([
            'api_key' => config('services.stripe.secret'),
            'stripe_version' => config('services.stripe.api_version'),
        ]);
    }

    public function start(
        PaymentMethod $method,
        int $amount,
        string $currency,
        array $context
    ): array {

        /* =========================
           CARD
        ========================= */
        if ($method === PaymentMethod::CARD) {
            $pi = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => strtolower($currency),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_id'   => (string)$context['order_id'],
                    // 'payment_id' => (string)$context['payment_id'],
                    'user_id'    => (string)$context['user_id'],
                    'shop_id'    => (string)$context['shop_id'],
                ],
            ]);

            return [
                'provider_payment_id' => $pi->id,
                'client_secret' => $pi->client_secret,
                'requires_action' => $pi->status === 'requires_action',
                'status' => $pi->status,
            ];
        }

        /* =========================
           KONBINI（本番仕様）
        ========================= */

        if ($method === PaymentMethod::KONBINI) {

            $payerName  = $context['payer_name'] ?? '購入者';
            $payerEmail = $context['payer_email'] ?? 'no-reply@example.com';

            $pi = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'jpy',

                // ★ 重要：confirm を true にする
                'confirm' => true,

                'payment_method_types' => ['konbini'],
                'payment_method_data' => [
                    'type' => 'konbini',
                    'billing_details' => [
                        'name'  => $payerName,
                        'email' => $payerEmail,
                    ],
                ],
                'metadata' => [
                    'order_id'   => (string)$context['order_id'],
                    // 'payment_id' => (string)$context['payment_id'],
                    'user_id'    => (string)$context['user_id'],
                    'shop_id'    => (string)$context['shop_id'],
                ],
            ]);

            $details = $pi->next_action->konbini_display_details ?? null;

            return [
                'provider_payment_id' => $pi->id,
                'client_secret' => $pi->client_secret,
                'requires_action' => true,
                'status' => $pi->status,
                'instructions' => [
                    'type' => 'konbini',
                    'expires_at' => $details?->expires_at,
                    'reference' => $details?->confirmation_number,
                    'store' => $details?->stores,
                ],
            ];
        }


        throw new \InvalidArgumentException('Unsupported payment method');
    }

    public function parseWebhook(string $payload, string $signature): array
    {
        $event = Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );

        if (!str_starts_with($event->type, 'payment_intent.')) {
            return [
                'ignored' => true,
                'provider_event_id' => $event->id,
                'event_type' => $event->type,
            ];
        }

        $pi = $event->data->object;

        return [
            'ignored' => false,
            'provider_event_id' => $event->id,
            'event_type' => $event->type,
            'provider_payment_id' => $pi->id,
            'status' => $pi->status,
        ];
    }

    public function createPaymentIntent(
        PaymentMethod $method,
        array $payload
    ): array {
        /**
         * payload は Application 層で構築済み
         * [
         *   amount,
         *   currency,
         *   shipping,
         *   metadata,
         * ]
         */

        // StripeClient にそのまま渡す
        $pi = $this->stripe->paymentIntents->create($payload);

        return [
            'provider_payment_id' => $pi->id,
            'client_secret' => $pi->client_secret,
            'requires_action' => $pi->status === 'requires_action',
            'status' => $pi->status,
        ];
    }
}
