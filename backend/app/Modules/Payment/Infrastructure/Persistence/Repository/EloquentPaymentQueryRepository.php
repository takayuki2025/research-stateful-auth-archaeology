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
        try {
            DB::table('payment_webhook_events')->insert([
                'provider'      => $provider,
                'event_id'      => $eventId,
                'event_type'    => $eventType,
                'payload_hash'  => $payloadHash,
                'status'        => 'processing',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            return true;

        } catch (QueryException $e) {

            // ✅ ここが重要：UNIQUE違反だけ「既処理扱い」にする
            // MySQL: 23000 / 1062, PostgreSQL: 23505
            $sqlState = $e->errorInfo[0] ?? null;
            $driverCode = $e->errorInfo[1] ?? null;

            if ($sqlState === '23000' || $sqlState === '23505' || $driverCode === 1062) {
                return false; // duplicate
            }

            // ❌ それ以外は「既処理」ではない。異常として投げる（safeReserveが飲む）
            throw $e;
        }
    }

    public function complete(
        string $provider,
        string $eventId,
        string $status,
        ?int $paymentId = null,
        ?int $orderId = null,
        ?string $errorMessage = null,
    ): void {
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
