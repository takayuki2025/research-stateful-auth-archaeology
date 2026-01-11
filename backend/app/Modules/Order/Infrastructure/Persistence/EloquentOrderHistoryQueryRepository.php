<?php

namespace App\Modules\Order\Infrastructure\Persistence;

use App\Modules\Order\Domain\Repository\OrderHistoryQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentOrderHistoryQueryRepository implements OrderHistoryQueryRepository
{
    public function findByBuyer(int $userId): array
    {
        return DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('items as i', 'i.id', '=', 'oi.item_id')
            ->where('o.user_id', $userId) // ← buyer
            ->orderByDesc('o.created_at')
            ->get([
                DB::raw("CONCAT('order_', o.id, '_item_', oi.id) as row_id"),
                'i.id as item_id',
                'o.id as order_id',
                'i.name',
                'i.item_image',
                'oi.price_amount as price',
            ])
            ->map(fn ($r) => [
                'row_id'     => (string) $r->row_id,
                'item_id'    => (int) $r->item_id,
                'order_id'   => (int) $r->order_id,
                'name'       => (string) $r->name,
                'item_image' => $r->item_image,
                'price'      => (int) $r->price,
            ])
            ->all();
    }
}