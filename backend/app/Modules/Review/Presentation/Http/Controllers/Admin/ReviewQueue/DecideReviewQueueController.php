<?php

namespace App\Modules\Review\Presentation\Http\Controllers\Admin\ReviewQueue;

use App\Http\Controllers\Controller;
use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use App\Modules\ProviderIntel\Application\UseCase\Admin\ReviewQueue\ApplyProviderIntelDecisionUseCase;
use App\Modules\Review\Domain\Repository\ReviewRequestForInfoRepository;
use App\Modules\Review\Application\UseCase\GenerateMissingInfoChecklistUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DecideReviewQueueController extends Controller
{
    public function __construct(
        private ReviewQueueRepository $queue,
        private ApplyProviderIntelDecisionUseCase $applyProviderIntel,
        private ReviewRequestForInfoRepository $requestsForInfo,
        private GenerateMissingInfoChecklistUseCase $generateChecklist,
    ) {
    }

    public function __invoke(Request $request, int $id)
    {
        $data = $request->validate([
            'action' => 'required|string|in:approve,reject,request_more_info',
            'note' => 'nullable|string',
            'extra' => 'nullable|array',
        ]);

        // X-Admin-Key 経由だと user がいないので null でOK
        $decidedBy = $request->user()?->id;

        DB::transaction(function () use ($id, $data, $decidedBy) {

            $action = $data['action'];

            // 0) item を読む（以降の分岐で必要）
            $item = $this->queue->get($id);
            if (!$item) {
                throw new \DomainException('ReviewQueueItem not found');
            }

            // 1) request_more_info（不足要求）
            if ($action === 'request_more_info') {

                // extra.checklist があれば採用、無ければ自動生成
                $checklist = $data['extra']['checklist'] ?? null;
                if (!is_array($checklist)) {
                    $checklist = $this->generateChecklist->handle($item);
                }

                $this->requestsForInfo->open($id, $checklist, $decidedBy);


                // ✅ 同一refの既存in_reviewを先に閉じる（ユニーク制約回避）
$this->queue->closeInReviewForSameRef(
    $item['queue_type'],
    $item['ref_type'],
    (int)$item['ref_id'],
    $id
);

// decided_* をクリアしてからin_review
$this->queue->clearDecision($id);
$this->queue->updateStatus($id, 'in_review');

                // 運用上は in_review にする（pendingのままでも可だが、手戻りが増える）
                $this->queue->updateStatus($id, 'in_review');

                // decided_* は残さない（request_more_infoは“決裁完了”ではないため）
                // note は必要なら別カラム/別テーブルに残してOK（MVPはopen checklist側で十分）
                return;
            }

            // 2) reject / approve は「決裁」なので decide を確定
            $this->queue->decide(
                $id,
                $action,
                $decidedBy,
                $data['note'] ?? null,
                $data['extra'] ?? null
            );

            // reject/approve の時点で open な不足要求が残っていれば閉じる（運用的に必須）
            $this->requestsForInfo->closeOpenByQueueItem($id, $decidedBy);

            // 3) approve のときだけ apply（providerintel の確定）
            if ($action !== 'approve') {
                return;
            }

            // providerintel & catalog_source だけ apply
            if (($item['queue_type'] ?? null) !== 'providerintel') return;
            if (($item['ref_type'] ?? null) !== 'catalog_source') return;

            $catalogSourceId = (int)$item['ref_id'];
            $newHash = $item['summary']['new_hash'] ?? null;

            $this->applyProviderIntel->handle(
                catalogSourceId: $catalogSourceId,
                newHash: is_string($newHash) ? $newHash : null,
                approvedBy: $decidedBy
            );
        });

        return response()->json(['ok' => true], 200);
    }
}