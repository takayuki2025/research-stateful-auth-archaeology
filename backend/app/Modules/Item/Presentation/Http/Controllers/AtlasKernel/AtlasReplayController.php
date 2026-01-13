<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Modules\Item\Application\UseCase\AtlasKernel\ReplayAnalysisRequestUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AtlasReplayController extends Controller
{
    public function __construct(
        private ReplayAnalysisRequestUseCase $useCase,
    ) {}

    public function replay(Request $request, string $shop_code, int $request_id): JsonResponse
    {
        $actor = auth()->user();
        if (!$actor) {
            abort(401);
        }

        $shop = Shop::where('shop_code', $shop_code)->firstOrFail();

        $actorRoleSlug = $actor->roles()
            ->wherePivot('shop_id', $shop->id)
            ->select('roles.slug')
            ->value('roles.slug');

        if (!$actorRoleSlug) {
            abort(403, 'Shop role not found');
        }

        $data = $request->validate([
            'version' => ['nullable', 'string', 'max:32'],
            'reason'  => ['nullable', 'string', 'max:255'],
        ]);

        $this->useCase->handle(
            originalRequestId: $request_id,
            requestedVersion: $data['version'] ?? 'v3_ai',
            actorRole: (string)$actorRoleSlug,
            actorUserId: (int)$actor->id,
            triggerReason: $data['reason'] ?? 'manual replay',
        );

        return response()->json(['status' => 'accepted']);
    }
}