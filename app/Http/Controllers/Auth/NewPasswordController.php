<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    private const OTP_EXPIRE_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;

    public function create(Request $request): View
    {
        return view('auth.reset-password', ['email' => $request->query('email', old('email'))]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $throttleKey = 'password-otp:' . mb_strtolower($request->string('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'Too many attempts. Please request a new code and try again shortly.',
            ]);
        }

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        $expired = ! $record || Carbon::parse($record->created_at)->addMinutes(self::OTP_EXPIRE_MINUTES)->isPast();

        if (! $record || $expired || ! Hash::check($request->otp, $record->token)) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'That code is invalid or has expired.',
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => "We can't find a user with that email address.",
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('success', 'Your password has been reset — please log in.');
    }
}
