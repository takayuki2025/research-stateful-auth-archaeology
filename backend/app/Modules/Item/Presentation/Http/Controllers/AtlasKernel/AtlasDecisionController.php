<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideAtlasReviewUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AtlasDecisionController extends Controller
{
    public function __construct(
        private DecideAtlasReviewUseCase $useCase
    ) {}

    public function decide(Request $request, string $shop_code, int $request_id): JsonResponse
    {
        $actor = $request->user();

        // role slug を shop_code で解決（あなたのRoleUser設計前提）
        $actorRole = collect($actor->formattedRoles() ?? [])
            ->first(fn ($r) => ($r['shop_id'] ?? null) !== null) // shop_idベースにしたいならここを shop_code解決に置換
            ['slug'] ?? null;

        // ※ここは既に「shop_id→shop_code解決」をあなたが作っているはずなので、
        // 　確実化するなら「shop_code→shop_id」→ role_user.shop_idで拾う方式に統一してください。
        if (!$actorRole) {
            abort(403, 'Shop role not found');
        }

        $decisionType  = (string) $request->input('decision_type');
        $afterSnapshot = $request->input('after_snapshot');
        $note          = $request->input('note');

        $this->useCase->handle(
            shopCode: $shop_code,
            analysisRequestId: $request_id,
            decisionType: $decisionType,
            afterSnapshot: is_array($afterSnapshot) ? $afterSnapshot : null,
            note: is_string($note) ? $note : null,
            actorUserId: (int) $actor->id,
            actorRole: (string) $actorRole,
        );

        return response()->json(['status' => 'ok']);
    }
}