<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceSessionsController extends Controller
{
    public function list(Request $request)
    {
        $user = $request->user();

        $sessions = $user->refreshTokens()
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'device_id',
                'device_name',
                'ip_address',
                'user_agent',
                'created_at',
                'revoked',
                'expires_at',
            ]);

        return response()->json([
            'sessions' => $sessions,
        ]);
    }
}
