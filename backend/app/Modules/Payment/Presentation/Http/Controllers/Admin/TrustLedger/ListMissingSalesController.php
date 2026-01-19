<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\ListMissingSalesUseCase;
use Illuminate\Http\Request;

final class ListMissingSalesController extends Controller
{
    public function __construct(
        private ListMissingSalesUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
            'currency' => 'sometimes|string|in:JPY',
            'shop_ids' => 'sometimes|string',
            'limit' => 'sometimes|integer|min:1|max:200',
            'cursor' => 'sometimes|string',
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
            limit: (int)($data['limit'] ?? 50),
            cursor: $data['cursor'] ?? null,
        );

        return response()->json($page->toArray(), 200);
    }
}