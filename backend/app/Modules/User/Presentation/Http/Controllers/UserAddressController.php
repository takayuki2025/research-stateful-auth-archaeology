<?php

namespace App\Modules\User\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\UseCase\GetPrimaryUserAddressUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\UserAddress;

final class UserAddressController extends Controller
{
    public function __construct(
        private GetPrimaryUserAddressUseCase $useCase
    ) {
    }

    /**
     * GET /api/user/addresses/primary
     */
    public function primary(Request $request)
    {
        $user = $request->user();

        \Log::info('PrimaryAddressController called', [
            'user_id' => $user?->id,
        ]);

        $address = UserAddress::where('user_id', $user->id)
            ->where('is_primary', true)
            ->first();

        \Log::info('PrimaryAddress query result', [
            'address' => $address?->toArray(),
        ]);

        return response()->json([
            'data' => $address,
        ]);
    }
}
