<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        Log::info('[VerifyEmail] start', [
            'user_id' => $request->user()->id,
        ]);

        if ($request->user()->hasVerifiedEmail()) {
            Log::info('[VerifyEmail] already verified');
        } else {
            $request->fulfill();
            event(new Verified($request->user()));
            Log::info('[VerifyEmail] verified');
        }

        return redirect()->away(
            config('app.frontend_url') . '/email/verified'
        );
    }
}
