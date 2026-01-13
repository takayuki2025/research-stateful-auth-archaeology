<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideAtlasReviewUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AtlasDecisionController extends Controller
{
    public function __construct(
        private DecideAtlasReviewUseCase $useCase,
    ) {}

    public function decide(Request $request, string $shop_code, int $request_id): JsonResponse
    {
        $actor = auth()->user();
        if (!$actor) {
            abort(401);
        }

        $shop = Shop::where('shop_code', $shop_code)->firstOrFail();

        // role_user(pivot) + roles.slug を shop_id で解決（あなたの構造に合わせる）
        $actorRoleSlug = $actor->roles()
            ->wherePivot('shop_id', $shop->id)
            ->select('roles.slug')
            ->value('roles.slug');

        if (!$actorRoleSlug) {
            abort(403, 'Shop role not found');
        }

        $data = $request->validate([
            'decision_type' => ['required', 'string', 'in:approve,reject,edit_confirm,manual_override,system_approve'],
            'note' => ['nullable', 'string', 'max:2000'],
            'after_snapshot' => ['nullable', 'array'],
        ]);

        $this->useCase->handle(
    shopCode: $shopCode,
    analysisRequestId: $requestId,
    decisionType: $request->input('decision_type'),
    afterSnapshot: $request->input('after_snapshot'),
    note: $request->input('note'),
    actorUserId: $actor->id,
    actorRole: $actorRole,
);

        return response()->json(['status' => 'ok']);
    }
}