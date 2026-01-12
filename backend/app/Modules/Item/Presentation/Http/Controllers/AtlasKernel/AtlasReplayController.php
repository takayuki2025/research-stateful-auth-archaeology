<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\AtlasKernel\ReplayAnalysisRequestUseCase;
use Illuminate\Http\Request;
use App\Models\Shop;

final class AtlasReplayController extends Controller
{
    public function __construct(
        private ReplayAnalysisRequestUseCase $useCase,
    ) {}

    public function replay(Request $request, string $shopCode, int $requestId)
    {
        $actor = auth()->user();

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

        $this->useCase->handle(
            originalRequestId: $requestId,
            requestedVersion: $request->input('version', 'v3_ai'),
            actorRole: $actorRoleSlug,
            actorUserId: $actor->id,
            triggerReason: 'manual replay'
        );

        return response()->json(['status' => 'accepted']);
    }
}