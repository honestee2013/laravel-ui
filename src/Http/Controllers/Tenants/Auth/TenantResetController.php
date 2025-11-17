<?php

namespace QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;

class TenantResetController extends Controller
{
    public function create()
    {
        return view('session/reset-password/sendEmail');
    }

    public function sendEmail(Request $request)
    {
        if(env('IS_DEMO')) {
            return redirect()->back()->withErrors(['msg2' => 'You are in a demo version, you can\'t recover your password.']);
        }

        $request->validate(['email' => 'required|email']);

        // Use default broker - it will use the current tenant database connection
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['success' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function resetPass($token)
    {
        return view('session/reset-password/resetPassword', ['token' => $token]);
    }
}