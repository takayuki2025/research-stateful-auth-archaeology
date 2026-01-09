<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

final class RegisterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // ✅ Sanctum Cookie SPA の核心
        Auth::login($user);
        $request->session()->regenerate();

        // 🔥 メール認証送信
        event(new Registered($user));

        return response()->json([
            'message' => 'registered',
        ], 201);
    }
}