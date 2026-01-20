<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\HandlePaymentWebhookUseCase;
use App\Modules\Payment\Application\UseCase\Wallet\HandleWalletWebhookUseCase;
use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Stripe\Webhook;

final class StripeWebhookController extends Controller
{
    public function __construct(
        private HandlePaymentWebhookUseCase $paymentUseCase,
        private HandleWalletWebhookUseCase $walletUseCase,
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

        // JSTで統一（あなたの方針）
        $occurredAt = (new \DateTimeImmutable('@' . $event->created))
            ->setTimezone(new \DateTimeZone(config('app.timezone')));

        $input = new HandlePaymentWebhookInput(
            provider: 'stripe',
            eventId: $event->id,
            eventType: $event->type,
            payload: $event->toArray(),
            payloadHash: hash('sha256', $payload),
            occurredAt: $occurredAt,
        );
\Log::info('[StripeWebhook] payload saved', [
    'event_id' => $input->eventId,
    'event_type' => $input->eventType,
    'payload_len' => strlen($payload),
]);
        // ✅ 受信イベントをDB保存（Replay用）
// 既存行がある場合は payload を埋める（今回のような payload=null を救済）
$raw = $payload; // すでに $payload = $request->getContent();

$hash = hash('sha256', $raw);

DB::table('payment_webhook_events')->updateOrInsert(
    ['provider' => 'stripe', 'event_id' => $input->eventId],
    [
        'event_type'   => $input->eventType,
        'payload_hash' => $hash,      // ✅ DBに保存するpayloadから計算
        'payload'      => $raw,       // ✅ rawそのまま
        'signature'    => $sig,
        // statusは既存運用によりけり。上書きしたくないなら削除可
        'status'       => 'received',
        'updated_at'   => now(),
        'created_at'   => now(),
    ]
);

        try {
            // Wallet系
            if (str_starts_with($event->type, 'setup_intent.') || str_starts_with($event->type, 'payment_method.')) {
                $this->walletUseCase->handle($input);
            } else {
                $this->paymentUseCase->handle($input);
            }
        } catch (\Throwable $e) {
            \Log::error('[Stripe Webhook UseCase Throwable Swallowed]', [
                'event_id'   => $input->eventId,
                'event_type' => $input->eventType,
                'message'    => $e->getMessage(),
            ]);
        }

        return response()->json(['ok' => true], 200);
    }
}