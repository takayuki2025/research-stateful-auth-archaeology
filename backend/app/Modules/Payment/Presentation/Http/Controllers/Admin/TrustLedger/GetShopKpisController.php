<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\GetShopKpisUseCase;
use Illuminate\Http\Request;

final class GetShopKpisController extends Controller
{
    public function __construct(
        private GetShopKpisUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
            'currency' => 'sometimes|string|in:JPY',
            'shop_ids' => 'sometimes|string', // "1,2,3"
        ]);

        $shopIds = null;
        if (!empty($data['shop_ids'])) {
            $shopIds = array_values(array_filter(array_map(
                fn ($v) => is_numeric($v) ? (int)$v : null,
                explode(',', $data['shop_ids'])
            )));
        }

        $page = $this->useCase->handle(
            shopIds: $shopIds,
            from: $data['from'],
            to: $data['to'],
            currency: $data['currency'] ?? 'JPY',
        );

        return response()->json($page->toArray(), 200);
    }
}