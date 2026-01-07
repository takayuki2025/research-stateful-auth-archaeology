<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()
        );
    }
}
