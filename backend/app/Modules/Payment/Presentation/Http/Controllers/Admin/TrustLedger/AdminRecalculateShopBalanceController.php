<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\AdminRecalculateShopBalanceUseCase;
use Illuminate\Http\Request;

final class AdminRecalculateShopBalanceController extends Controller
{
    public function __construct(
        private AdminRecalculateShopBalanceUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request, int $shopId)
    {
        $data = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
            'currency' => 'sometimes|string|in:JPY',
        ]);

        $accountId = $this->useCase->handle(
            shopId: $shopId,
            from: $data['from'],
            to: $data['to'],
            currency: $data['currency'] ?? 'JPY',
        );

        return response()->json(['ok' => true, 'account_id' => $accountId], 200);
    }
}