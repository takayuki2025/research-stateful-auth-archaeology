<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository;

use App\Modules\Payment\Domain\Repository\PaymentQueryRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentPaymentQueryRepository implements PaymentQueryRepository
{
    public function reserve(
        string $provider,
        string $eventId,
        string $eventType,
        string $payloadHash
    ): bool {
        // ✅ 1) processed_webhook_events にロックを取る（冪等の正：R5）
        try {
            DB::table('processed_webhook_events')->insert([
                'provider'     => $provider,
                'event_id'     => $eventId,
                'event_type'   => $eventType,
                'payload_hash' => $payloadHash,
                'status'       => 'reserved',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            $driverCode = $e->errorInfo[1] ?? null;

            if ($sqlState === '23000' || $sqlState === '23505' || $driverCode === 1062) {
                return false; // duplicate = already reserved/processed
            }
            throw $e;
        }

        // ✅ 2) payment_webhook_events は観測ログ（最小：今のUNIQUEを維持しつつinsert）
        // ここがUNIQUEで落ちても冪等ロックは取れているので、例外は握り潰して良い
        try {
            DB::table('payment_webhook_events')->insert([
                'provider'     => $provider,
                'event_id'     => $eventId,
                'event_type'   => $eventType,
                'payload_hash' => $payloadHash,
                'status'       => 'processing',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable) {
            // swallow
        }

        return true;
    }

    public function complete(
        string $provider,
        string $eventId,
        string $status,
        ?int $paymentId = null,
        ?int $orderId = null,
        ?string $errorMessage = null,
    ): void {
        // ✅ processed_webhook_events を確定
        DB::table('processed_webhook_events')
            ->where('provider', $provider)
            ->where('event_id', $eventId)
            ->update([
                'status'        => $status,
                'payment_id'    => $paymentId,
                'order_id'      => $orderId,
                'error_message' => $errorMessage,
                'processed_at'  => now(),
                'updated_at'    => now(),
            ]);

        // ✅ payment_webhook_events はログ更新（存在しない場合があるので update のみ）
        DB::table('payment_webhook_events')
            ->where('provider', $provider)
            ->where('event_id', $eventId)
            ->update([
                'status'        => $status,
                'payment_id'    => $paymentId,
                'order_id'      => $orderId,
                'error_message' => $errorMessage,
                'updated_at'    => now(),
            ]);
    }
}
