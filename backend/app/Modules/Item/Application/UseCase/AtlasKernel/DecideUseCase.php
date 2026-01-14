<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Repository\ItemDraftRepository;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\DecisionLedgerRepository;
use App\Modules\Item\Domain\Enum\ReviewDecisionType;
use Illuminate\Support\Facades\DB;

final class DecideUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
        private ItemDraftRepository $drafts,
        private ReviewDecisionRepository $decisions,
        private DecisionLedgerRepository $ledgers,
    ) {}

    /**
     * @param int $analysisRequestId
     * @param string $decisionType approve | edit_confirm | reject
     * @param array|null $afterOverride edit_confirm のみ。例: ['brand' => 'Apple', 'condition' => '新品']
     * @param string|null $note 任意メモ
     * @param array{type?:string,id?:int}|null $actor 任意。例: ['type'=>'user','id'=>7]
     */
    public function handle(
        int $analysisRequestId,
        string $decisionType,
        ?array $afterOverride = null,
        ?string $note = null,
        ?array $actor = null,
    ): void {
        DB::transaction(function () use ($analysisRequestId, $decisionType, $afterOverride, $note, $actor) {

            // 1) Request（主語）
            $request = $this->requests->findOrFail($analysisRequestId);

            // $draftId = $request->itemDraftId(); // ★ Domain Entity に必須
            // if (! $draftId) {
            //     throw new \RuntimeException('analysis_request has no item_draft_id');
            // }

            // 2) BEFORE（SoT）: item_drafts 一択
            $draft = $this->drafts->findById($draftId);
            if (! $draft) {
                throw new \RuntimeException("item_draft not found: {$draftId}");
            }

            $before = [
                'brand'     => $draft->brandRaw(),      // string|null
                'condition' => $draft->conditionRaw(),  // string|null
                'color'     => null,                    // ★ B固定：Draftは持たない
            ];

            // 3) AFTER（AI提案）: analysis_results 一択（requestId 主語）
            $result = $this->results->findByRequestId($analysisRequestId);
            if (! $result) {
                throw new \RuntimeException('analysis_result not found');
            }

            $after = [
                'brand'     => $result->brandName ?? null,
                'condition' => $result->conditionName ?? null,
                'color'     => $result->colorName ?? null,
            ];

            // 4) decisionType 正規化/検証
            $type = ReviewDecisionType::fromString($decisionType); // 例外投げてOK（400変換はControllerで）
            // approve / edit_confirm / reject

            // 5) edit_confirm の override を AFTER に反映（許可キー制限）
            $afterSnapshot = $after;
            if ($type->isEditConfirm()) {
                $override = $this->sanitizeAfterOverride($afterOverride ?? []);
                $afterSnapshot = array_merge($afterSnapshot, $override);
            }

            // 6) diff 自動生成（v3固定）
            $diff = [];
            foreach (['brand', 'condition', 'color'] as $key) {
                $b = $before[$key] ?? null;
                $a = $afterSnapshot[$key] ?? null;
                if (($b ?? '') !== ($a ?? '')) {
                    $diff[$key] = ['before' => $b, 'after' => $a];
                }
            }

            // 7) ReviewDecision 保存（監査ログ）
            $decisionId = $this->decisions->create([
                'analysis_request_id' => $analysisRequestId,
                'decision_type'       => $type->value,                 // approve/edit_confirm/reject
                'before_snapshot'     => $before,                      // array
                'after_snapshot'      => $type->isReject() ? null : $afterSnapshot, // rejectはnullでもOK（運用次第）
                'diff'                => $diff,
                'note'                => $note,
                'decided_by_type'     => $actor['type'] ?? null,
                'decided_by'          => $actor['id'] ?? null,
                'decided_at'          => now(),
            ]);

            // 8) DecisionLedger（最終確定台帳）— 「採用結果」を必ず記録
            //    approve/edit_confirm は採用、reject は棄却として記録
            $this->ledgers->append([
                'analysis_request_id' => $analysisRequestId,
                'review_decision_id'  => $decisionId,
                'decision'            => $type->value,
                'final_snapshot'      => $type->isReject() ? $before : $afterSnapshot,
                'before_snapshot'     => $before,
                'after_snapshot'      => $afterSnapshot,
                'diff'                => $diff,
                'overall_confidence'  => (float)($result->overallConfidence ?? 0.0),
                'confidence_map'      => $this->normalizeConfidenceMap($result->confidenceMap ?? null),
                'note'                => $note,
                'actor_type'          => $actor['type'] ?? null,
                'actor_id'            => $actor['id'] ?? null,
                'created_at'          => now(),
            ]);

            // 9) SoT反映（Draft更新）
            //    - approve/edit_confirm: Draftに反映（Draftが持つ範囲のみ）
            //    - reject: 反映しない（before維持）
            if (! $type->isReject()) {
                $this->applyToDraftSoT_B($draft, $afterSnapshot);
                $this->drafts->save($draft);
            }

            // 10) 任意：request の status 更新（UIの完了表示用）
            // ここは好みだが、v3では「decision が付いたら決着」として done/closed に寄せるのが安定
            $this->requests->appendEvent($analysisRequestId, 'decided', [
                'decision' => $type->value,
                'review_decision_id' => $decisionId,
            ]);
        });
    }

    /**
     * edit_confirm で受け付けるキーを制限（事故防止）
     * - v3対象: brand / condition / color
     */
    private function sanitizeAfterOverride(array $afterOverride): array
    {
        $allowed = ['brand', 'condition', 'color'];
        $clean = [];

        foreach ($allowed as $k) {
            if (array_key_exists($k, $afterOverride)) {
                $v = $afterOverride[$k];
                $clean[$k] = is_string($v) ? trim($v) : null;
                if ($clean[$k] === '') {
                    $clean[$k] = null;
                }
            }
        }

        return $clean;
    }

    /**
     * B固定：Draftに color は書かない
     * - brand/condition は Draft が持つので反映
     * - color は entity_tags/analysis_results 側の世界として保持
     */
    private function applyToDraftSoT_B($draft, array $afterSnapshot): void
    {
        // Draft の実装に合わせてメソッド名は調整してください
        // ここでは「Raw setter」がある前提で書いています

        if (array_key_exists('brand', $afterSnapshot)) {
            $draft->setBrandRaw($afterSnapshot['brand']); // string|null
        }
        if (array_key_exists('condition', $afterSnapshot)) {
            $draft->setConditionRaw($afterSnapshot['condition']); // string|null
        }

        // ★ B固定：color は反映しない
        // if (array_key_exists('color', $afterSnapshot)) { $draft->setColorRaw($afterSnapshot['color']); }
    }

    private function normalizeConfidenceMap(mixed $confidenceMap): array
    {
        if (is_string($confidenceMap)) {
            $decoded = json_decode($confidenceMap, true);
            $confidenceMap = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($confidenceMap)) {
            $confidenceMap = [];
        }

        // UI が期待するキー（brand/color/condition）に寄せる
        return [
            'brand'     => isset($confidenceMap['brand']) ? (float)$confidenceMap['brand'] : null,
            'color'     => isset($confidenceMap['color']) ? (float)$confidenceMap['color'] : null,
            'condition' => isset($confidenceMap['condition']) ? (float)$confidenceMap['condition'] : null,
        ];
    }
}
