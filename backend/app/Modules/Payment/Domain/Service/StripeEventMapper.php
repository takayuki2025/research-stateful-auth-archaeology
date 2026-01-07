<?php

namespace App\Modules\Payment\Domain\Service;

use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;
use App\Modules\Payment\Domain\Event\DomainPaymentEvent;
use App\Modules\Payment\Domain\Event\DomainPaymentEventType;

final class StripeEventMapper
{
    public function map(HandlePaymentWebhookInput $input): DomainPaymentEvent
    {
        $payload = $input->payload;
        $object  = $payload['data']['object'] ?? [];

        // payment_intent id を取得
        $providerPaymentId = $this->extractPaymentIntentId($input->eventType, $object);

        if (!is_string($providerPaymentId) || $providerPaymentId === '') {
            return DomainPaymentEvent::ignored($input->occurredAt);
        }

        $instructions = $this->extractKonbiniInstructions($object);


        return match ($input->eventType) {

            // ----------------------------
            // PaymentIntent 系（唯一の成功）
            // ----------------------------
            'payment_intent.succeeded' =>
                new DomainPaymentEvent(
                    DomainPaymentEventType::SUCCEEDED,
                    $providerPaymentId,
                    null,
                    $input->occurredAt,
                    $instructions,
                ),

            'payment_intent.payment_failed' =>
                new DomainPaymentEvent(
                    DomainPaymentEventType::FAILED,
                    $providerPaymentId,
                    $object['last_payment_error']['message'] ?? null,
                    $input->occurredAt,
                    $instructions,
                ),

            'payment_intent.requires_action',
            'payment_intent.created' =>
                new DomainPaymentEvent(
                    DomainPaymentEventType::REQUIRES_ACTION,
                    $providerPaymentId,
                    null,
                    $input->occurredAt,
                    $instructions,
                ),

            // ----------------------------
            // 補助イベント（必ず無視）
            // ----------------------------
            'charge.succeeded',
            'checkout.session.completed' =>
                DomainPaymentEvent::ignored($input->occurredAt),

            // ----------------------------
            // Refund（別系統なのでOK）
            // ----------------------------
            'charge.refunded' =>
                new DomainPaymentEvent(
                    DomainPaymentEventType::REFUND_SUCCEEDED,
                    $providerPaymentId,
                    null,
                    $input->occurredAt,
                    [
                        'provider' => 'stripe',
                        'provider_refund_id' =>
                            $object['refunds']['data'][0]['id'] ?? null,
                        'reason' => 'stripe_webhook',
                    ],
                ),

            default =>
                DomainPaymentEvent::ignored($input->occurredAt),
        };

    }

    private function extractPaymentIntentId(string $eventType, array $object): ?string
    {
        if (str_starts_with($eventType, 'payment_intent.')) {
            return $object['id'] ?? null;
        }

        if (in_array($eventType, ['charge.succeeded', 'charge.refunded', 'checkout.session.completed'], true)) {
            return $object['payment_intent'] ?? null;
        }

        return null;
    }

    private function extractKonbiniInstructions(array $piObject): ?array
    {
        $details = $piObject['next_action']['konbini_display_details'] ?? null;
        if (!is_array($details)) {
            return null;
        }

        return [
            'type' => 'konbini',
            'expires_at' => $details['expires_at'] ?? null,
            'store' => [
                $details['store'] ?? '' => [
                    'confirmation_number' => $details['confirmation_number'] ?? null,
                ],
            ],
        ];
    }
}
