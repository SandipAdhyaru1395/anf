<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm(): View
    {
        $pageConfigs = ['myLayout' => 'blank'];

        return view('auth.forgot-password', ['pageConfigs' => $pageConfigs]);
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            ['email.required' => 'Please enter your email address.']
        );

        $user = User::where('email', $request->input('email'))->first();
        if ($user && $user->status === 'inactive') {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This account is not active. Please contact an administrator.']);
        }

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        if ($status !== Password::RESET_THROTTLED && $status !== Password::INVALID_USER) {
            Log::warning('Admin password reset link failed', [
                'email' => $request->input('email'),
                'status' => $status,
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        $pageConfigs = ['myLayout' => 'blank'];

        return view('auth.reset-password', [
            'pageConfigs' => $pageConfigs,
            'token' => $token,
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'token' => ['required', 'string'],
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            [
                'password.min' => 'Your password must be at least 8 characters.',
            ]
        );

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
