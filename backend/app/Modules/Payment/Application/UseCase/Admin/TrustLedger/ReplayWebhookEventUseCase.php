<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;
use App\Modules\Payment\Application\UseCase\HandlePaymentWebhookUseCase;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class ReplayWebhookEventUseCase
{
    public function __construct(
        private HandlePaymentWebhookUseCase $handler,
    ) {
    }

    public function handle(string $eventId): array
    {
        return DB::transaction(function () use ($eventId) {

            // 1) 保存済みの受信イベントを取得（payload必須）
            $event = DB::table('payment_webhook_events')
                ->where('event_id', $eventId)
                ->first();

            if (!$event) {
                abort(404, 'Webhook event not found.');
            }

            if (!$event->payload) {
                abort(409, 'Payload not stored. Cannot replay.');
            }

            // 2) payload を配列として復元（DBのJSON型は環境で string/object になることがあるので強制整形）
            $payloadArr = is_array($event->payload)
                ? $event->payload
                : json_decode((string) $event->payload, true);

            if (!is_array($payloadArr)) {
                abort(409, 'Stored payload is not valid JSON.');
            }

            // 3) payload_hash 整合性チェック（改ざん/不整合防止）
            //    ※ “受信時と同じルール” に必ず揃える：ここでは raw JSON を sha256
            $raw = (string) $event->payload;

$computedHash = hash('sha256', $raw);
if ($computedHash !== (string) $event->payload_hash) {
    abort(409, 'Payload hash mismatch. Refuse replay.');
}

$payloadArr = json_decode($raw, true);
if (!is_array($payloadArr)) {
    abort(409, 'Stored payload is not valid JSON.');
}

            // 4) occurredAt を Stripe payload から確定（Stripe event: created は UNIX seconds）
            //    無ければ DB created_at を使う（最後の保険）
            $createdUnix = $payloadArr['created'] ?? null;

            if (is_int($createdUnix) || (is_string($createdUnix) && ctype_digit($createdUnix))) {
                $occurredAt = (new DateTimeImmutable())->setTimestamp((int) $createdUnix);
            } else {
                // created_at が null の可能性もあるので fallback
                $occurredAt = $event->created_at
                    ? new DateTimeImmutable((string) $event->created_at)
                    : new DateTimeImmutable();
            }

            // 5) 冪等テーブルを “replay 用にリセット”
            //    UNIQUE 制約で insert が死ぬのを回避しつつ、handler が「未処理」に見える状態に戻す
            DB::table('processed_webhook_events')
                ->where('event_id', $eventId)
                ->update([
                    'status' => 'reserved',
                    'error_code' => null,
                    'error_message' => null,
                    'processed_at' => null,
                    'updated_at' => now(),
                ]);

            // 6) ✅ HandlePaymentWebhookInput を「完全一致」で生成
            $input = new HandlePaymentWebhookInput(
                provider: (string) $event->provider,                       // "stripe"
                eventId: (string) ($payloadArr['id'] ?? $eventId),         // evt_...
                eventType: (string) ($payloadArr['type'] ?? $event->event_type),
                payload: $payloadArr,
                payloadHash: (string) $event->payload_hash,
                occurredAt: $occurredAt,
            );

            // 7) 再処理
            $this->handler->handle($input);

            return [
                'status' => 'replayed',
                'event_id' => $eventId,
                'occurred_at' => $occurredAt->format(DATE_ATOM),
            ];
        });
    }
}