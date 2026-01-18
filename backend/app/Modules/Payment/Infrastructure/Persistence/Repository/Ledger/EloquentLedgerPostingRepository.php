<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\LedgerPostingRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentLedgerPostingRepository implements LedgerPostingRepository
{
    public function reserve(
        string $sourceProvider,
        string $sourceEventId,
        int $shopId,
        ?int $orderId,
        ?int $paymentId,
        string $postingType,
        int $amount,
        string $currency,
        \DateTimeImmutable $occurredAt,
        ?array $meta = null,
    ): ?int {
        try {
            return (int) DB::table('ledger_postings')->insertGetId([
                'shop_id' => $shopId,
                'source_provider' => $sourceProvider,
                'source_event_id' => $sourceEventId,
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'posting_type' => $postingType,
                'amount' => $amount,
                'currency' => $currency,
                'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
                'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {
            // UNIQUE違反なら既処理（冪等）
            $sqlState = $e->errorInfo[0] ?? null;
            $driverCode = $e->errorInfo[1] ?? null;
            if ($sqlState === '23000' || $sqlState === '23505' || $driverCode === 1062) {
                return null;
            }
            throw $e;
        }
    }
}