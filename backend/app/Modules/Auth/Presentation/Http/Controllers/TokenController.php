<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    /**
     * 🚫 今回は refresh を提供しない
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'message' => 'Refresh token is not supported in current auth mode'
        ], 501);
    }
}
