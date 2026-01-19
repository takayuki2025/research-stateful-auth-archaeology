<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\GetGlobalKpiUseCase;
use Illuminate\Http\Request;

final class GetGlobalKpiController extends Controller
{
    public function __construct(
        private GetGlobalKpiUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
            'currency' => 'sometimes|string|in:JPY',
        ]);

        $dto = $this->useCase->handle(
            from: $data['from'],
            to: $data['to'],
            currency: $data['currency'] ?? 'JPY',
        );

        return response()->json($dto->toArray(), 200);
    }
}