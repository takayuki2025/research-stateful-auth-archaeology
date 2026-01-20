<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

final class DevIssueJwtController extends Controller
{
    public function __invoke(Request $request)
    {
        // email か user_id のどちらかで発行できるようにする
        $data = $request->validate([
            'email' => 'nullable|email',
            'user_id' => 'nullable|integer',
            'ttl_seconds' => 'nullable|integer|min:60|max:86400',
        ]);

        if (empty($data['email']) && empty($data['user_id'])) {
            return response()->json(['message' => 'email or user_id is required'], 422);
        }

        $user = null;

        if (!empty($data['user_id'])) {
            $user = User::find((int)$data['user_id']);
        } else {
            $user = User::where('email', $data['email'])->first();
        }

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $secret = config('jwt.secret');
        if (!$secret) {
            return response()->json(['message' => 'jwt.secret is not configured'], 500);
        }

        $ttl = (int)($data['ttl_seconds'] ?? 3600);
        $now = time();

        // ★重要：UserProvisioningService(provisionFromExternalIdentity) が email を要求する実装でも
        // ここで email を入れておけば “既存ユーザーへ紐付け” で安全に通る
        $payload = [
            'iss' => 'custom',                 // provider 判定用（あなたの JwtTokenVerifier は provider='custom' を返しているので整合）
            'sub' => (string)$user->id,        // numericでも string で保持（将来のIdaaSを見据えて）
            'email' => $user->email,
            'name' => $user->name,
            'iat' => $now,
            'exp' => $now + $ttl,
        ];

        $jwt = JWT::encode($payload, $secret, 'HS256');

        return response()->json([
            'token' => $jwt,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user_id' => $user->id,
        ]);
    }
}