<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\GetShopKpisUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // ---- ここから追加：shop_name / shop_type / owner_name を items に付与（N+1回避） ----
        $arr = $page->toArray();
        $items = $arr['items'] ?? [];

        $ids = array_values(array_unique(array_filter(array_map(
            fn ($s) => isset($s['shop_id']) ? (int)$s['shop_id'] : null,
            $items
        ), fn ($v) => is_int($v) && $v > 0)));

        $shopMap = [];
        if (count($ids) > 0) {
            $rows = DB::table('shops')
                ->leftJoin('users', 'users.id', '=', 'shops.owner_user_id')
                ->whereIn('shops.id', $ids)
                ->select([
                    'shops.id as shop_id',
                    'shops.name as shop_name',
                    'shops.type as shop_type',
                    'users.name as owner_name',
                ])
                ->get();

            foreach ($rows as $r) {
                $shopMap[(int)$r->shop_id] = [
                    'shop_name' => $r->shop_name,
                    'shop_type' => $r->shop_type,
                    'owner_name' => $r->owner_name,
                ];
            }
        }

        foreach ($items as &$s) {
            $sid = isset($s['shop_id']) ? (int)$s['shop_id'] : 0;
            $s['shop_name'] = $shopMap[$sid]['shop_name'] ?? null;
            $s['shop_type'] = $shopMap[$sid]['shop_type'] ?? null;
            $s['owner_name'] = $shopMap[$sid]['owner_name'] ?? null;
        }
        unset($s);

        $arr['items'] = $items;

        return response()->json($arr, 200);
    }
}