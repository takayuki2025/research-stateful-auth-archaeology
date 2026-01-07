<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Shop\Domain\Entity\Shop;

final class ShopShowController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var Shop|null $shop */
        $shop = $request->attributes->get('currentShop');

        if (!$shop) {
            abort(404, 'Shop not found');
        }

        return response()->json([
            'shop' => [
                'id'          => $shop->id(),
                'shop_code'   => $shop->shopCode(),
                'name'        => $shop->name(),
                'status'      => $shop->status()->value,
            ],
        ]);
    }
}
