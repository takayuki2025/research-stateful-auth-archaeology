<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user(); // ★これが必須

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'display_name' => $user->name,
            'email_verified_at' => $user->email_verified_at,
            'profile_completed' => $user->profile_completed, // ★必須
        ]);
    }
}