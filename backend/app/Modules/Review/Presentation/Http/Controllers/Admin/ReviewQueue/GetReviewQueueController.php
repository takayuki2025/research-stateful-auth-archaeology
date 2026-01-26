<?php

namespace App\Modules\Review\Presentation\Http\Controllers\Admin\ReviewQueue;

use App\Http\Controllers\Controller;
use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class GetReviewQueueController extends Controller
{
    public function __construct(
        private ReviewQueueRepository $queue,
    ) {
    }

    public function __invoke(Request $request, int $id)
    {
        // 1) まず item を取得
        $item = $this->queue->get($id);
        if (!$item) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // 2) pending のものだけ “閲覧開始” として in_review に寄せたい場合
        //    ※あなたの運用が「閲覧したらin_review」ならこのままでOK
        //    もし “閲覧は副作用なし” にしたいなら、以下のブロックを削除してください。
        if (($item['status'] ?? null) === 'pending') {
            DB::transaction(function () use ($item, $id) {
                // ✅ 同一refの既存in_reviewを閉じる（ユニーク制約回避）
                $this->queue->closeInReviewForSameRef(
                    (string)$item['queue_type'],
                    (string)$item['ref_type'],
                    (int)$item['ref_id'],
                    $id
                );

                // ✅ 自分を in_review にする
                $this->queue->updateStatus($id, 'in_review');
            });

            // 状態更新後の最新を取り直す
            $item = $this->queue->get($id) ?? $item;
        }

        return response()->json($item, 200);
    }
}