<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\MarkPayoutStatusAdminUseCase;
use Illuminate\Http\Request;

final class MarkPayoutStatusAdminController extends Controller
{
    public function __construct(
        private MarkPayoutStatusAdminUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request, int $payoutId)
    {
        $data = $request->validate([
            'status' => 'required|string|in:requested,processing,paid,failed',
        ]);

        $this->useCase->handle($payoutId, (string)$data['status']);

        return response()->json(['ok' => true], 200);
    }
}