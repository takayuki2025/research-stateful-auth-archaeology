<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\HandlePaymentWebhookUseCase;
use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Webhook;

final class StripeWebhookController extends Controller
{
    public function __construct(
        private HandlePaymentWebhookUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request): Response
    {

        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            \Log::warning('[Stripe Webhook Invalid Signature]', [
                'message' => $e->getMessage(),
            ]);
            return response('Invalid signature', 400);
        }

        $input = new HandlePaymentWebhookInput(
            provider: 'stripe',
            eventId: $event->id,
            eventType: $event->type,
            payload: $event->toArray(),
            payloadHash: hash('sha256', $payload),
            occurredAt: new \DateTimeImmutable('@' . $event->created),
        );

        // ★最後の砦：UseCase 内で何が起きても 200 を返す
        try {
            $this->useCase->handle($input);
        } catch (\Throwable $e) {
            \Log::error('[Stripe Webhook UseCase Throwable Swallowed]', [
                'event_id'   => $input->eventId,
                'event_type' => $input->eventType,
                'message'    => $e->getMessage(),
            ]);
            // throw しない
        }

        return response()->json(['ok' => true], 200);
    }
}
