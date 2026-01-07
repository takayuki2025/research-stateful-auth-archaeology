<?php

namespace App\Modules\Order\Infrastructure\Persistence;

use App\Modules\Order\Domain\Repository\OrderHistoryQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentOrderHistoryQueryRepository implements OrderHistoryQueryRepository
{
    public function findByBuyer(int $userId): array
    {
        return DB::table('order_histories')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'row_id'     => $r->order_id . '-' . $r->item_id,
                'item_id'    => (int) $r->item_id,
                'order_id'   => (int) $r->order_id,
                'name'       => (string) $r->item_name,
                'item_image' => $r->item_image,
                'price'      => (int) $r->price_amount,
            ])
            ->all();
    }
}
