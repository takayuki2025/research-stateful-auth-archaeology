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
    \Log::info('[login_or_register] start');

    $data = $request->validate([
        'id_token' => 'required|string',
    ]);

    \Log::info('[login_or_register] validated', [
        'token_len' => strlen($data['id_token']),
    ]);

    \Log::info('[login_or_register] decoding...');
    $decoded = $this->verifier->decode($data['id_token']);
    \Log::info('[login_or_register] decoded', [
        'provider' => $decoded->provider ?? null,
    ]);

    $p = $decoded->payload;

    $firebaseUid = (string)($p->sub ?? '');
    $email = $p->email ?? null;
    $emailVerified = (bool)($p->email_verified ?? false);
    $displayName = $p->name ?? null;

    \Log::info('[login_or_register] provisioning...', [
        'uid' => $firebaseUid,
        'email' => $email,
        'verified' => $emailVerified,
    ]);

    $provisioned = $this->provisioning->provisionFromFirebase(
        firebaseUid: $firebaseUid,
        email: $email,
        emailVerified: $emailVerified,
        displayName: $displayName,
    );

    \Log::info('[login_or_register] provisioned', [
        'user_id' => $provisioned->userId ?? null,
    ]);

    return response()->json([
        'token_type' => 'Bearer',
        'access_token' => $data['id_token'],
        'user_id' => $provisioned->userId,
    ]);
}
}