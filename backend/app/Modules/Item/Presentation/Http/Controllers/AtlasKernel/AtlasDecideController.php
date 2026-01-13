<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\DecideAtlasReviewUseCase;
use App\Models\Shop;
use Illuminate\Http\Request;

final class AtlasDecideController extends Controller
{
    public function __construct(
        private DecideAtlasReviewUseCase $useCase,
    ) {}

    public function decide(Request $request, string $shopCode, int $requestId)
    {
        $actor = auth()->user();
        if (!$actor) {
            abort(401);
        }

        $shop = Shop::where('shop_code', $shopCode)->firstOrFail();

        $actorRoleSlug = $actor
            ->shopRoles()
            ->where('shop_id', $shop->id)
            ->with('role')
            ->first()
            ?->role
            ?->slug;

        if ($actorRoleSlug === null) {
            abort(403, 'Shop role not found');
        }

        $validated = $request->validate([
            'decisionType' => 'required|string|in:approve,system_approve,reject,edit_confirm,manual_override',
            'afterSnapshot' => 'nullable|array',
            'note' => 'nullable|string|max:2000',
        ]);

        $this->useCase->handle(
            shopCode: $shopCode,
            analysisRequestId: $requestId,
            decisionType: $validated['decisionType'],
            afterSnapshot: $validated['afterSnapshot'] ?? null,
            note: $validated['note'] ?? null,
            actorUserId: (int)$actor->id,
            actorRole: (string)$actorRoleSlug,
        );

        return response()->json(['status' => 'accepted']);
    }
}