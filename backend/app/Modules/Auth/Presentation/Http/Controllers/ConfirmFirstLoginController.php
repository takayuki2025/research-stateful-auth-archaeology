<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ConfirmFirstLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email not verified'], 403);
        }

        if (empty($user->first_login_at)) {
            $user->forceFill(['first_login_at' => now()])->save();
        }

        return response()->json(['ok' => true], 200);
    }
}