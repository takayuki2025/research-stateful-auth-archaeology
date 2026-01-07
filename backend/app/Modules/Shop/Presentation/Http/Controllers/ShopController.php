<?php

namespace App\Modules\Shop\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Shop\Application\UseCase\CreateShopUseCase;
use App\Modules\Shop\Application\UseCase\GetMyShopUseCase;
use App\Modules\Shop\Application\Dto\CreateShopInput;

final class ShopController extends Controller
{
    public function create(
        Request $request,
        CreateShopUseCase $useCase
    ) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $input = new CreateShopInput(
            ownerUserId: $request->user()->id,
            name: $request->input('name'),
        );

        $output = $useCase->handle($input);

        return response()->json($output->toArray(), 201);
    }

    public function me(
        Request $request,
        GetMyShopUseCase $useCase
    ) {
        $shop = $useCase->handle($request->user()->id);

        return response()->json([
            'shop' => $shop?->toArray(),
        ]);
    }
}
