<?php

namespace App\Modules\Order\Infrastructure\Persistence;

use App\Modules\Order\Domain\Repository\OrderHistoryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentOrderHistoryRepository implements OrderHistoryRepository
{
    public function addEvent(int $orderId, string $type, ?array $payload = null): void
    {
        DB::table('order_events')->insert([
            'order_id'    => $orderId,
            'type'        => $type,
            'payload'     => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function findByBuyer(int $buyerId): array
    {
        return [];
    }
}
