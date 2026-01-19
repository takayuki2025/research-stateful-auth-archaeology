<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\ReplaySaleUseCase;
use Illuminate\Http\Request;

final class ReplaySaleController extends Controller
{
    public function __construct(
        private ReplaySaleUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|integer|min:1',
        ]);

        $this->useCase->handle((int)$data['payment_id']);

        return response()->json(['ok' => true], 200);
    }
}