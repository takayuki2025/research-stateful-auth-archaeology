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

        $providerPaymentId = $this->extractPaymentIntentId($input->eventType, $object);

        if (!is_string($providerPaymentId) || $providerPaymentId === '') {
            return DomainPaymentEvent::ignored($input->occurredAt);
        }

        $instructions = $this->extractKonbiniInstructions($object);

        return match ($input->eventType) {

            // ✅ 成功（最重要）
            'payment_intent.succeeded',
            'charge.succeeded' =>
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

            // Refund
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

            // ✅ v2-3.2: refund 実額（最小単位、JPYなら円）
            'refund_amount' =>
                $object['refunds']['data'][0]['amount'] ?? null,

            // ✅ currency（念のため）
            'currency' =>
                strtoupper($object['currency'] ?? 'jpy'),

            'reason' => 'stripe_webhook',
        ],

        
    ),

    'refund.updated' =>
    new DomainPaymentEvent(
        DomainPaymentEventType::REFUND_SUCCEEDED,
        $providerPaymentId,
        null,
        $input->occurredAt,
        [
            'provider' => 'stripe',
            'provider_refund_id' => $object['id'] ?? null,
            'refund_amount' => $object['amount'] ?? null,
            'currency' => strtoupper($object['currency'] ?? 'jpy'),
            'reason' => 'stripe_refund.updated',
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

    if (str_starts_with($eventType, 'charge.')) {
        return $object['payment_intent'] ?? null;
    }

    // ✅ 追加：refund.* は object.payment_intent
    if (str_starts_with($eventType, 'refund.')) {
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