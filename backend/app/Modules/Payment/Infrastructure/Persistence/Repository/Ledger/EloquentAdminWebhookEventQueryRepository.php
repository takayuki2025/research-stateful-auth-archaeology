<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository\Ledger;

use App\Modules\Payment\Domain\Ledger\Repository\AdminWebhookEventQueryRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAdminWebhookEventQueryRepository implements AdminWebhookEventQueryRepository
{
    public function searchWebhookEvents(
        string $from,
        string $to,
        ?string $status,
        ?string $eventType,
        ?string $q,
        int $limit,
        ?string $cursor
    ): array {
        $limit = max(1, min($limit, 200));

        $qb = DB::table('processed_webhook_events')
            ->select([
                'id',
                'provider',
                'event_id',
                'event_type',
                'status',
                'payment_id',
                'order_id',
                'error_message',
                'created_at',
            ])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if (is_string($status) && $status !== '') {
            $qb->where('status', $status);
        }
        if (is_string($eventType) && $eventType !== '') {
            $qb->where('event_type', $eventType);
        }
        if (is_string($q) && $q !== '') {
            $qq = trim($q);
            $qb->where(function ($w) use ($qq) {
                $w->where('event_id', 'like', $qq . '%')
                  ->orWhere('event_type', 'like', $qq . '%');
                if (ctype_digit($qq)) {
                    $w->orWhere('payment_id', (int)$qq)
                      ->orWhere('order_id', (int)$qq);
                }
            });
        }
        if (is_string($cursor) && ctype_digit($cursor)) {
            $qb->where('id', '<', (int)$cursor);
        }

        $rows = $qb->orderByDesc('id')->limit($limit + 1)->get();

        $items = [];
        foreach ($rows->take($limit) as $r) {
            $items[] = [
                'id' => (int)$r->id,
                'provider' => (string)$r->provider,
                'event_id' => (string)$r->event_id,
                'event_type' => (string)$r->event_type,
                'status' => (string)$r->status,
                'payment_id' => $r->payment_id !== null ? (int)$r->payment_id : null,
                'order_id' => $r->order_id !== null ? (int)$r->order_id : null,
                'error_message' => $r->error_message !== null ? (string)$r->error_message : null,
                'created_at' => (string)$r->created_at,
            ];
        }

        $next = null;
        if ($rows->count() > $limit) {
            $next = (string)$rows->last()->id;
        }

        return ['items' => $items, 'next_cursor' => $next];
    }
}