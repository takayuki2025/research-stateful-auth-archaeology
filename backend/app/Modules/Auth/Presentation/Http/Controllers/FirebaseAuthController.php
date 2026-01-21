<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use App\Modules\Auth\Domain\Port\UserProvisioningPort;

final class FirebaseAuthController extends Controller
{
    public function __construct(
        private TokenVerifierPort $verifier,
        private UserProvisioningPort $provisioning,
    ) {}

    public function loginOrRegister(Request $request)
    {
        $data = $request->validate([
            'id_token' => 'required|string',
        ]);

        // ✅ JWKS検証（サービスアカウント不要）
        $decoded = $this->verifier->decode($data['id_token']);

        // providerが firebase であることを要求（混入防止）
        if ($decoded->provider !== 'firebase') {
            return response()->json(['message' => 'Invalid token provider'], 401);
        }

        $p = $decoded->payload;

        // Firebase securetoken の必要項目
        $firebaseUid = (string)($p->sub ?? '');
        $email = $p->email ?? null;
        $emailVerified = (bool)($p->email_verified ?? false);
        $displayName = $p->name ?? null;

        if ($firebaseUid === '') {
            return response()->json(['message' => 'Invalid firebase token'], 401);
        }

        // ✅ Provisioning（既存の仕組みを活用）
        $provisioned = $this->provisioning->provisionFromFirebase(
            firebaseUid: $firebaseUid,
            email: $email,
            emailVerified: $emailVerified,
            displayName: $displayName,
        );

        // 最小：フロントはこのid_tokenをそのまま Bearer として使ってよい
        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $data['id_token'],
            'user_id' => $provisioned->userId,
        ]);
    }
}